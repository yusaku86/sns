<?php

namespace App\Application\Retweet;

use App\Domain\Retweet\Repositories\RetweetRepositoryInterface;

class RetweetPostUseCase
{
    public function __construct(
        private RetweetRepositoryInterface $retweetRepository,
    ) {}

    public function execute(string $userId, string $postId): void
    {
        if ($this->retweetRepository->exists($userId, $postId)) {
            return;
        }

        $this->retweetRepository->save($userId, $postId);
    }
}
