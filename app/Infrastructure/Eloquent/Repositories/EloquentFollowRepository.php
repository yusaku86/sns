<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Follow\Entities\FollowUser;
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

    public function getFollowers(string $userId, ?string $authUserId = null): array
    {
        $follows = FollowModel::where('following_id', $userId)
            ->with('follower')
            ->get();

        $followerIds = $follows->pluck('follower_id')->all();

        $followedByAuth = [];
        if ($authUserId && count($followerIds) > 0) {
            $followedByAuth = FollowModel::where('follower_id', $authUserId)
                ->whereIn('following_id', $followerIds)
                ->pluck('following_id')
                ->flip()
                ->all();
        }

        return $follows->map(function ($follow) use ($followedByAuth) {
            $user = $follow->follower;

            return new FollowUser(
                id: $user->id,
                name: $user->name,
                handle: $user->handle,
                profileImageUrl: $user->profile_image_url,
                isFollowedByAuthUser: isset($followedByAuth[$user->id]),
            );
        })->all();
    }

    public function getFollowing(string $userId, ?string $authUserId = null): array
    {
        $follows = FollowModel::where('follower_id', $userId)
            ->with('following')
            ->get();

        $followingIds = $follows->pluck('following_id')->all();

        $followedByAuth = [];
        if ($authUserId && count($followingIds) > 0) {
            $followedByAuth = FollowModel::where('follower_id', $authUserId)
                ->whereIn('following_id', $followingIds)
                ->pluck('following_id')
                ->flip()
                ->all();
        }

        return $follows->map(function ($follow) use ($followedByAuth) {
            $user = $follow->following;

            return new FollowUser(
                id: $user->id,
                name: $user->name,
                handle: $user->handle,
                profileImageUrl: $user->profile_image_url,
                isFollowedByAuthUser: isset($followedByAuth[$user->id]),
            );
        })->all();
    }
}
