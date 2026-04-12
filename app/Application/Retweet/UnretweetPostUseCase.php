<?php

namespace App\Application\Retweet;

use App\Domain\Retweet\Repositories\RetweetRepositoryInterface;

class UnretweetPostUseCase
{
    public function __construct(
        private RetweetRepositoryInterface $retweetRepository,
    ) {}

    public function execute(string $userId, string $postId): void
    {
        $this->retweetRepository->delete($userId, $postId);
    }
}
