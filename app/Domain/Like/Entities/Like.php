<?php

namespace App\Domain\Like\Entities;

use DateTimeImmutable;

/**
 * いいねドメインエンティティ。ユーザーと投稿の関係を表す。
 */
class Like
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $postId,
        public readonly DateTimeImmutable $createdAt,
    ) {}
}
