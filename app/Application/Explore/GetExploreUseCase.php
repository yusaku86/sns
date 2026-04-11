<?php

namespace App\Application\Explore;

use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;

class GetExploreUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
    ) {}

    /** @return Post[] */
    public function execute(?string $authUserId = null, int $limit = 20): array
    {
        return $this->postRepository->getAll($authUserId, $limit);
    }
}
