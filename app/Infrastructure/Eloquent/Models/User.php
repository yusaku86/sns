<?php

namespace App\Infrastructure\Eloquent\Models;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * ユーザーのEloquentモデル。認証・2FAを含む。
 *
 * @property string $id
 * @property string $name
 * @property string $handle
 * @property string|null $bio
 * @property string|null $header_image
 * @property string|null $profile_image
 * @property string $email
 * @property CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property CarbonImmutable|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, Follow> $followers
 * @property-read int|null $followers_count
 * @property-read Collection<int, Follow> $followings
 * @property-read int|null $followings_count
 * @property-read Collection<int, Like> $likes
 * @property-read int|null $likes_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Post> $posts
 * @property-read int|null $posts_count
 * @property-read mixed $profile_image_url
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereHandle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereHeaderImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProfileImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['name', 'handle', 'email', 'password', 'bio', 'header_image', 'profile_image'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $appends = ['profile_image_url'];

    /**
     * プロフィール画像の公開URLを返すアクセサ。
     *
     * @return Attribute<string|null, never>
     */
    protected function profileImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->profile_image
                ? Storage::disk('public')->url($this->profile_image)
                : null,
        );
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id ??= (string) Str::uuid();
            if (empty($model->handle)) {
                $base = preg_replace('/[^a-z0-9_]/', '', strtolower(explode('@', $model->email)[0])) ?: 'user';
                $model->handle = $base.'_'.substr(str_replace('-', '', (string) Str::uuid()), 0, 6);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * 投稿一覧へのリレーション。
     *
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * いいね一覧へのリレーション。
     *
     * @return HasMany<Like, $this>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * フォロー中ユーザー一覧へのリレーション。
     *
     * @return HasMany<Follow, $this>
     */
    public function followings(): HasMany
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    /**
     * フォロワー一覧へのリレーション。
     *
     * @return HasMany<Follow, $this>
     */
    public function followers(): HasMany
    {
        return $this->hasMany(Follow::class, 'following_id');
    }
}
