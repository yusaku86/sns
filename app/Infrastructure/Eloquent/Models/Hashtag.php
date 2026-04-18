<?php

namespace App\Infrastructure\Eloquent\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * ハッシュタグのEloquentモデル。
 *
 * @property string $id
 * @property string $name
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, Post> $posts
 * @property-read int|null $posts_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['id', 'name'])]
class Hashtag extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($model) => $model->id ??= (string) Str::uuid());
    }

    /**
     * このハッシュタグが付いた投稿一覧へのリレーション。
     *
     * @return BelongsToMany<Post, $this>
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}
