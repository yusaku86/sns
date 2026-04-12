<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Retweet\Repositories\RetweetRepositoryInterface;
use App\Infrastructure\Eloquent\Models\Retweet as RetweetModel;

class EloquentRetweetRepository implements RetweetRepositoryInterface
{
    public function exists(string $userId, string $postId): bool
    {
        return RetweetModel::where('user_id', $userId)
            ->where('post_id', $postId)
            ->exists();
    }

    public function save(string $userId, string $postId): void
    {
        RetweetModel::create([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
    }

    public function delete(string $userId, string $postId): void
    {
        RetweetModel::where('user_id', $userId)
            ->where('post_id', $postId)
            ->delete();
    }
}
