<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Follow\Repositories\FollowRepositoryInterface;
use App\Infrastructure\Eloquent\Models\Follow as FollowModel;

class EloquentFollowRepository implements FollowRepositoryInterface
{
    public function exists(string $followerId, string $followingId): bool
    {
        return FollowModel::where('follower_id', $followerId)
            ->where('following_id', $followingId)
            ->exists();
    }

    public function save(string $followerId, string $followingId): void
    {
        FollowModel::create([
            'follower_id' => $followerId,
            'following_id' => $followingId,
        ]);
    }

    public function delete(string $followerId, string $followingId): void
    {
        FollowModel::where('follower_id', $followerId)
            ->where('following_id', $followingId)
            ->delete();
    }
}
