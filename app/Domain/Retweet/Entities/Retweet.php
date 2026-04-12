<?php

namespace App\Domain\Retweet\Entities;

use DateTimeImmutable;

class Retweet
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $postId,
        public readonly DateTimeImmutable $createdAt,
    ) {}
}
