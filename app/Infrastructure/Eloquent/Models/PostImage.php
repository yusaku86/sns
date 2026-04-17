<?php

namespace App\Infrastructure\Eloquent\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $post_id
 * @property string $path
 * @property int $order
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Post $post
 *
 * @mixin \Eloquent
 */
#[Fillable(['id', 'post_id', 'path', 'order'])]
class PostImage extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($model) => $model->id ??= (string) Str::uuid());
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
