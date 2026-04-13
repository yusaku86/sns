<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Like\Repositories\LikeRepositoryInterface;
use App\Domain\Post\Entities\Post as PostEntity;
use App\Infrastructure\Eloquent\Models\Like as LikeModel;
use App\Infrastructure\Eloquent\Models\Post as PostModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class EloquentLikeRepository implements LikeRepositoryInterface
{
    public function exists(string $userId, string $postId): bool
    {
        return LikeModel::where('user_id', $userId)
            ->where('post_id', $postId)
            ->exists();
    }

    public function save(string $userId, string $postId): void
    {
        LikeModel::create([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
    }

    public function delete(string $userId, string $postId): void
    {
        LikeModel::where('user_id', $userId)
            ->where('post_id', $postId)
            ->delete();
    }

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
        $posts = PostModel::with(['user', 'hashtags'])
            ->withCount(['likes', 'replies', 'retweets'])
            ->whereIn('id', $postIds)
            ->get()
            ->sortBy(fn (PostModel $p) => $postIds->search($p->id));

        return $posts
            ->map(fn (PostModel $model) => $this->toPostEntity($model, $authUserId))
            ->values()
            ->all();
    }

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
        );
    }
}
