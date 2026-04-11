<?php

namespace App\Domain\Like\Repositories;

interface LikeRepositoryInterface
{
    public function exists(string $userId, string $postId): bool;

    public function save(string $userId, string $postId): void;

    public function delete(string $userId, string $postId): void;
}
