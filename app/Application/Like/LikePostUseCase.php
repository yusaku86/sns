<?php

namespace App\Application\Like;

use App\Domain\Like\Repositories\LikeRepositoryInterface;

class LikePostUseCase
{
    public function __construct(
        private LikeRepositoryInterface $likeRepository,
    ) {}

    public function execute(string $userId, string $postId): void
    {
        if ($this->likeRepository->exists($userId, $postId)) {
            return;
        }

        $this->likeRepository->save($userId, $postId);
    }
}
