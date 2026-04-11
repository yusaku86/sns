<?php

namespace App\Domain\Follow\Entities;

use DateTimeImmutable;

class Follow
{
    public function __construct(
        public readonly string $id,
        public readonly string $followerId,
        public readonly string $followingId,
        public readonly DateTimeImmutable $createdAt,
    ) {}
}
