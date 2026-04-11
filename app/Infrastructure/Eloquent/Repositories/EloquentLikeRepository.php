<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Like\Repositories\LikeRepositoryInterface;
use App\Infrastructure\Eloquent\Models\Like as LikeModel;

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
}
