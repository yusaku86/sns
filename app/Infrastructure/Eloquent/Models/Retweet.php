<?php

namespace App\Infrastructure\Eloquent\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * リツイートのEloquentモデル。updated_atを持たない。
 *
 * @property string $id
 * @property string $user_id
 * @property string $post_id
 * @property string|null $created_at
 * @property-read Post $post
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retweet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retweet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retweet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retweet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retweet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retweet wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retweet whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['id', 'user_id', 'post_id'])]
class Retweet extends Model
{
    use HasFactory;

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

    /**
     * リツイートしたユーザーへのリレーション。
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * リツイートされた投稿へのリレーション。
     *
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
