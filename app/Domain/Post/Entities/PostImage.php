<?php

namespace App\Domain\Post\Entities;

/**
 * 投稿に添付された画像のドメインエンティティ。
 */
class PostImage
{
    public function __construct(
        public readonly string $id,
        public readonly string $postId,
        public readonly string $path,
        public readonly int $order,
    ) {}
}
