<?php

namespace App\Domain\Like\Repositories;

use App\Domain\Post\Entities\Post;

interface LikeRepositoryInterface
{
    public function exists(string $userId, string $postId): bool;

    public function save(string $userId, string $postId): void;

    public function delete(string $userId, string $postId): void;

    /**
     * ユーザーがいいねした投稿一覧
     *
     * @return Post[]
     */
    public function getLikedPostsByUserId(string $userId, ?string $authUserId = null, int $limit = 20): array;
}
