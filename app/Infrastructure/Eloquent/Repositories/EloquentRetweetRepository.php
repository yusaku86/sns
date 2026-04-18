<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Retweet\Repositories\RetweetRepositoryInterface;
use App\Infrastructure\Eloquent\Models\Retweet as RetweetModel;

/**
 * Eloquentを使ったリツイートリポジトリの実装。
 */
class EloquentRetweetRepository implements RetweetRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function exists(string $userId, string $postId): bool
    {
        return RetweetModel::where('user_id', $userId)
            ->where('post_id', $postId)
            ->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function save(string $userId, string $postId): void
    {
        RetweetModel::create([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $userId, string $postId): void
    {
        RetweetModel::where('user_id', $userId)
            ->where('post_id', $postId)
            ->delete();
    }
}
