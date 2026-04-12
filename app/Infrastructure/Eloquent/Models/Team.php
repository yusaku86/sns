<?php

namespace App\Infrastructure\Eloquent\Models;

use App\Concerns\GeneratesUniqueTeamSlugs;
use App\Enums\TeamRole;
use Carbon\CarbonImmutable;
use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property bool $is_personal
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Collection<int, TeamInvitation> $invitations
 * @property-read int|null $invitations_count
 * @property-read Membership|null $pivot
 * @property-read Collection<int, User> $members
 * @property-read int|null $members_count
 * @property-read Collection<int, Membership> $memberships
 * @property-read int|null $memberships_count
 *
 * @method static \Database\Factories\TeamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereIsPersonal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team withoutTrashed()
 *
 * @mixin \Eloquent
 */
#[Fillable(['name', 'slug', 'is_personal'])]
class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use GeneratesUniqueTeamSlugs, HasFactory, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Team $team) {
            $team->id ??= (string) Str::uuid();

            if (empty($team->slug)) {
                $team->slug = static::generateUniqueTeamSlug($team->name);
            }
        });

        static::updating(function (Team $team) {
            if ($team->isDirty('name')) {
                $team->slug = static::generateUniqueTeamSlug($team->name, $team->id);
            }
        });
    }

    public function owner(): ?Model
    {
        return $this->members()
            ->wherePivot('role', TeamRole::Owner->value)
            ->first();
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members', 'team_id', 'user_id')
            ->using(Membership::class)
            ->withPivot(['role'])
            ->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    protected function casts(): array
    {
        return [
            'is_personal' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
