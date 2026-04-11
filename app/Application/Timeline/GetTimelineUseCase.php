<?php

namespace App\Application\Timeline;

use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;

class GetTimelineUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
    ) {}

    /** @return Post[] */
    public function execute(string $userId, int $limit = 20): array
    {
        return $this->postRepository->getTimeline($userId, $limit);
    }
}
