<?php

namespace App\Concerns;

use App\Enums\TeamPermission;
use App\Enums\TeamRole;
use App\Infrastructure\Eloquent\Models\Membership;
use App\Infrastructure\Eloquent\Models\Team;
use App\Support\TeamPermissions;
use App\Support\UserTeam;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

/**
 * ユーザーモデルにチーム関連の機能を追加するトレイト。
 */
trait HasTeams
{
    /**
     * ユーザーが所属する全チームへのリレーション。
     *
     * @return BelongsToMany<Team, $this>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members', 'user_id', 'team_id')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    /**
     * ユーザーがオーナーのチーム一覧へのリレーション。
     *
     * @return HasManyThrough<Team, Membership, $this>
     */
    public function ownedTeams(): HasManyThrough
    {
        return $this->hasManyThrough(
            Team::class,
            Membership::class,
            'user_id',
            'id',
            'id',
            'team_id',
        )->where('team_members.role', TeamRole::Owner->value);
    }

    /**
     * ユーザーのチームメンバーシップ一覧へのリレーション。
     *
     * @return HasMany<Membership, $this>
     */
    public function teamMemberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'user_id');
    }

    /**
     * 現在のチームへのリレーション。
     *
     * @return BelongsTo<Team, $this>
     */
    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    /**
     * ユーザーのパーソナルチームを返す。
     */
    public function personalTeam(): ?Team
    {
        return $this->teams()
            ->where('is_personal', true)
            ->first();
    }

    /**
     * 指定チームに切り替える。所属していない場合はfalseを返す。
     *
     * @param  Team  $team  切り替え先チーム
     */
    public function switchTeam(Team $team): bool
    {
        if (! $this->belongsToTeam($team)) {
            return false;
        }

        $this->update(['current_team_id' => $team->id]);
        $this->setRelation('currentTeam', $team);

        URL::defaults(['current_team' => $team->slug]);

        return true;
    }

    /**
     * 指定チームに所属しているか確認する。
     *
     * @param  Team  $team  対象チーム
     */
    public function belongsToTeam(Team $team): bool
    {
        return $this->teams()->where('teams.id', $team->id)->exists();
    }

    /**
     * 指定チームが現在のチームか確認する。
     *
     * @param  Team  $team  対象チーム
     */
    public function isCurrentTeam(Team $team): bool
    {
        return $this->current_team_id === $team->id;
    }

    /**
     * 指定チームのオーナーか確認する。
     *
     * @param  Team  $team  対象チーム
     */
    public function ownsTeam(Team $team): bool
    {
        return $this->teamRole($team) === TeamRole::Owner;
    }

    /**
     * 指定チームでのユーザーのロールを返す。
     *
     * @param  Team  $team  対象チーム
     */
    public function teamRole(Team $team): ?TeamRole
    {
        return $this->teamMemberships()
            ->where('team_id', $team->id)
            ->first()
            ?->role;
    }

    /**
     * ユーザーが所属するチームをUserTeamオブジェクトのコレクションで返す。
     *
     * @param  bool  $includeCurrent  現在のチームを含めるか
     * @return Collection<int, UserTeam>
     */
    public function toUserTeams(bool $includeCurrent = false): Collection
    {
        return $this->teams()
            ->get()
            ->map(fn (Team $team) => ! $includeCurrent && $this->isCurrentTeam($team) ? null : $this->toUserTeam($team))
            ->filter()
            ->values();
    }

    /**
     * 指定チームをUserTeamオブジェクトに変換して返す。
     *
     * @param  Team  $team  対象チーム
     */
    public function toUserTeam(Team $team): UserTeam
    {
        $role = $this->teamRole($team);

        return new UserTeam(
            id: $team->id,
            name: $team->name,
            slug: $team->slug,
            isPersonal: $team->is_personal,
            role: $role?->value,
            roleLabel: $role?->label(),
            isCurrent: $this->isCurrentTeam($team),
        );
    }

    /**
     * 指定チームでのユーザー権限をTeamPermissionsオブジェクトで返す。
     *
     * @param  Team  $team  対象チーム
     */
    public function toTeamPermissions(Team $team): TeamPermissions
    {
        $role = $this->teamRole($team);

        return new TeamPermissions(
            canUpdateTeam: $role?->hasPermission(TeamPermission::UpdateTeam) ?? false,
            canDeleteTeam: $role?->hasPermission(TeamPermission::DeleteTeam) ?? false,
            canAddMember: $role?->hasPermission(TeamPermission::AddMember) ?? false,
            canUpdateMember: $role?->hasPermission(TeamPermission::UpdateMember) ?? false,
            canRemoveMember: $role?->hasPermission(TeamPermission::RemoveMember) ?? false,
            canCreateInvitation: $role?->hasPermission(TeamPermission::CreateInvitation) ?? false,
            canCancelInvitation: $role?->hasPermission(TeamPermission::CancelInvitation) ?? false,
        );
    }

    /**
     * 指定チームを除いたフォールバック先チームを返す。
     *
     * @param  Team|null  $excluding  除外するチーム
     */
    public function fallbackTeam(?Team $excluding = null): ?Team
    {
        return $this->teams()
            ->when($excluding, fn ($query) => $query->where('teams.id', '!=', $excluding->id))
            ->orderByRaw('LOWER(teams.name)')
            ->first();
    }

    /**
     * 指定チームで指定権限を持っているか確認する。
     *
     * @param  Team  $team  対象チーム
     * @param  TeamPermission  $permission  確認する権限
     */
    public function hasTeamPermission(Team $team, TeamPermission $permission): bool
    {
        return $this->teamRole($team)?->hasPermission($permission) ?? false;
    }
}
