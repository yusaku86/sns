<?php

namespace App\Domain\Post\Repositories;

use App\Domain\Post\Entities\Post;

interface PostRepositoryInterface
{
    public function findById(string $id, ?string $authUserId = null): ?Post;

    /** @return Post[] */
    public function getTimeline(string $userId, int $limit = 20): array;

    /** @return Post[] */
    public function getAll(?string $authUserId = null, int $limit = 20): array;

    public function save(Post $post): void;

    public function delete(string $id): void;
}
