<?php

namespace App\Application\Like;

use App\Domain\Like\Repositories\LikeRepositoryInterface;

class UnlikePostUseCase
{
    public function __construct(
        private LikeRepositoryInterface $likeRepository,
    ) {}

    public function execute(string $userId, string $postId): void
    {
        $this->likeRepository->delete($userId, $postId);
    }
}
