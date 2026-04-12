<?php

namespace App\Infrastructure\Eloquent\Models;

use App\Concerns\HasTeams;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'handle', 'email', 'password', 'bio', 'current_team_id'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, HasTeams, Notifiable, TwoFactorAuthenticatable;

    public $incrementing = false;

    protected $keyType = 'string';

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

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function followings(): HasMany
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function followers(): HasMany
    {
        return $this->hasMany(Follow::class, 'following_id');
    }
}
