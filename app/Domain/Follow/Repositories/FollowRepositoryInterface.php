<?php

namespace App\Domain\Follow\Repositories;

use App\Domain\Follow\Entities\FollowUser;

interface FollowRepositoryInterface
{
    public function exists(string $followerId, string $followingId): bool;

    public function save(string $followerId, string $followingId): void;

    public function delete(string $followerId, string $followingId): void;

    /**
     * @return FollowUser[]
     */
    public function getFollowers(string $userId, ?string $authUserId = null): array;

    /**
     * @return FollowUser[]
     */
    public function getFollowing(string $userId, ?string $authUserId = null): array;
}
