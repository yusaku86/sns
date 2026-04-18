<?php

namespace App\Infrastructure\Eloquent\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * 投稿添付画像のEloquentモデル。
 *
 * @property string $id
 * @property string $post_id
 * @property string $path
 * @property int $order
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Post $post
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereUpdatedAt($value)
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

    /**
     * 画像が属する投稿へのリレーション。
     *
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
