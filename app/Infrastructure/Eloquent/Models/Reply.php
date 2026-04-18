<?php

namespace App\Infrastructure\Eloquent\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * リプライのEloquentモデル。
 *
 * @property string $id
 * @property string $post_id
 * @property string $user_id
 * @property string $content
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Post $post
 * @property-read User $user
 *
 * @method static \Database\Factories\ReplyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reply newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reply query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reply whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reply whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reply whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reply wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reply whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reply whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['id', 'post_id', 'user_id', 'content'])]
class Reply extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($model) => $model->id ??= (string) Str::uuid());
    }

    /**
     * リプライ先の投稿へのリレーション。
     *
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * リプライ投稿者へのリレーション。
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
