<?php

namespace App\Infrastructure\Eloquent\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['id', 'follower_id', 'following_id'])]
class Follow extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = null;

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id ??= (string) Str::uuid();
            $model->created_at ??= now();
        });
    }

    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function following(): BelongsTo
    {
        return $this->belongsTo(User::class, 'following_id');
    }
}
