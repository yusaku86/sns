<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Like\Repositories\LikeRepositoryInterface;
use App\Domain\Post\Entities\Post as PostEntity;
use App\Domain\Post\Entities\PostImage as PostImageEntity;
use App\Infrastructure\Eloquent\Models\Like as LikeModel;
use App\Infrastructure\Eloquent\Models\Post as PostModel;
use App\Infrastructure\Eloquent\Models\PostImage as PostImageModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Eloquentを使ったいいねリポジトリの実装。
 */
class EloquentLikeRepository implements LikeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function exists(string $userId, string $postId): bool
    {
        return LikeModel::where('user_id', $userId)
            ->where('post_id', $postId)
            ->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function save(string $userId, string $postId): void
    {
        LikeModel::create([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $userId, string $postId): void
    {
        LikeModel::where('user_id', $userId)
            ->where('post_id', $postId)
            ->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function getLikedPostsByUserId(string $userId, ?string $authUserId = null, int $limit = 20): array
    {
        $postIds = LikeModel::where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->pluck('post_id');

        if ($postIds->isEmpty()) {
            return [];
        }

        /** @var Collection<int, PostModel> $posts */
        $posts = PostModel::with(['user', 'hashtags', 'images'])
            ->withCount(['likes', 'replies', 'retweets'])
            ->whereIn('id', $postIds)
            ->get()
            ->sortBy(fn (PostModel $p) => $postIds->search($p->id));

        return $posts
            ->map(fn (PostModel $model) => $this->toPostEntity($model, $authUserId))
            ->values()
            ->all();
    }

    /**
     * PostモデルからPostエンティティを生成する。
     *
     * @param  PostModel  $model  投稿モデル
     * @param  string|null  $authUserId  認証ユーザーID
     */
    private function toPostEntity(PostModel $model, ?string $authUserId): PostEntity
    {
        $likedByAuthUser = $authUserId
            ? $model->likes()->where('user_id', $authUserId)->exists()
            : false;

        $retweetedByAuthUser = $authUserId
            ? $model->retweets()->where('user_id', $authUserId)->exists()
            : false;

        return new PostEntity(
            id: $model->id,
            userId: $model->user_id,
            userName: $model->user->name,
            userHandle: $model->user->handle,
            content: $model->content,
            createdAt: new \DateTimeImmutable($model->created_at),
            likesCount: $model->likes_count,
            likedByAuthUser: $likedByAuthUser,
            repliesCount: $model->replies_count,
            retweetsCount: $model->retweets_count,
            retweetedByAuthUser: $retweetedByAuthUser,
            hashtags: $model->hashtags->pluck('name')->all(),
            userProfileImageUrl: $model->user->profile_image
                ? Storage::disk('public')->url($model->user->profile_image)
                : null,
            images: $model->images
                ->map(fn (PostImageModel $img) => new PostImageEntity(
                    id: $img->id,
                    postId: $img->post_id,
                    path: $img->path,
                    order: $img->order,
                ))
                ->all(),
        );
    }
}
