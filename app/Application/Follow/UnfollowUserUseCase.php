<?php

namespace App\Application\Follow;

use App\Domain\Follow\Repositories\FollowRepositoryInterface;

class UnfollowUserUseCase
{
    public function __construct(
        private FollowRepositoryInterface $followRepository,
    ) {}

    public function execute(string $followerId, string $followingId): void
    {
        $this->followRepository->delete($followerId, $followingId);
    }
}
