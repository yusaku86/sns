<?php

namespace App\Domain\User\Entities;

class User
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $bio,
        public readonly int $postsCount,
        public readonly int $followersCount,
        public readonly int $followingCount,
        public readonly bool $isFollowedByAuthUser,
    ) {}
}
