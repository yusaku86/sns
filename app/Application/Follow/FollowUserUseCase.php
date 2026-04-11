<?php

namespace App\Application\Follow;

use App\Domain\Follow\Repositories\FollowRepositoryInterface;
use InvalidArgumentException;

class FollowUserUseCase
{
    public function __construct(
        private FollowRepositoryInterface $followRepository,
    ) {}

    public function execute(string $followerId, string $followingId): void
    {
        if ($followerId === $followingId) {
            throw new InvalidArgumentException('自分自身をフォローすることはできません。');
        }

        if ($this->followRepository->exists($followerId, $followingId)) {
            return;
        }

        $this->followRepository->save($followerId, $followingId);
    }
}
