<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Post\Entities\Post as PostEntity;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Infrastructure\Eloquent\Models\Follow;
use App\Infrastructure\Eloquent\Models\Post as PostModel;

class EloquentPostRepository implements PostRepositoryInterface
{
    public function findById(string $id, ?string $authUserId = null): ?PostEntity
    {
        $model = PostModel::with('user')->withCount(['likes', 'replies'])->find($id);

        if (! $model) {
            return null;
        }

        return $this->toEntity($model, $authUserId);
    }

    public function getTimeline(string $userId, int $limit = 20): array
    {
        $followingIds = Follow::where('follower_id', $userId)
            ->pluck('following_id');

        return PostModel::with('user')
            ->withCount(['likes', 'replies'])
            ->whereIn('user_id', $followingIds)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($model) => $this->toEntity($model, $userId))
            ->all();
    }

    public function getAll(?string $authUserId = null, int $limit = 20): array
    {
        return PostModel::with('user')
            ->withCount(['likes', 'replies'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($model) => $this->toEntity($model, $authUserId))
            ->all();
    }

    public function save(PostEntity $post): void
    {
        PostModel::create([
            'id' => $post->id,
            'user_id' => $post->userId,
            'content' => $post->content,
        ]);
    }

    public function delete(string $id): void
    {
        PostModel::destroy($id);
    }

    private function toEntity(PostModel $model, ?string $authUserId): PostEntity
    {
        $likedByAuthUser = $authUserId
            ? $model->likes()->where('user_id', $authUserId)->exists()
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
        );
    }
}
