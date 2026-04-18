<?php

namespace App\Infrastructure\Eloquent\Models;

use App\Enums\TeamRole;
use Carbon\CarbonImmutable;
use Database\Factories\TeamInvitationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * チーム招待のEloquentモデル。ランダムコードで招待URLを生成する。
 *
 * @property string $id
 * @property string $code
 * @property string $team_id
 * @property string $email
 * @property TeamRole $role
 * @property string $invited_by
 * @property CarbonImmutable|null $expires_at
 * @property CarbonImmutable|null $accepted_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $inviter
 * @property-read Team|null $team
 *
 * @method static \Database\Factories\TeamInvitationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereInvitedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['team_id', 'email', 'role', 'invited_by', 'expires_at', 'accepted_at'])]
class TeamInvitation extends Model
{
    /** @use HasFactory<TeamInvitationFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TeamInvitation $invitation) {
            $invitation->id ??= (string) Str::uuid();

            if (empty($invitation->code)) {
                $invitation->code = Str::random(64);
            }
        });
    }

    /**
     * 招待先チームへのリレーション。
     *
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * 招待を送ったユーザーへのリレーション。
     *
     * @return BelongsTo<User, $this>
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * 招待が承認済みか確認する。
     */
    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    /**
     * 招待が承認待ち（未承認かつ未期限切れ）か確認する。
     */
    public function isPending(): bool
    {
        return $this->accepted_at === null && ! $this->isExpired();
    }

    /**
     * 招待が期限切れか確認する。
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    protected function casts(): array
    {
        return [
            'role' => TeamRole::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    /**
     * ルートモデルバインディングのキーとしてcodeを使用する。
     */
    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
