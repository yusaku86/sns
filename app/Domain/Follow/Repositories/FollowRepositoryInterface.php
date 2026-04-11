<?php

namespace App\Domain\Follow\Repositories;

interface FollowRepositoryInterface
{
    public function exists(string $followerId, string $followingId): bool;

    public function save(string $followerId, string $followingId): void;

    public function delete(string $followerId, string $followingId): void;
}
