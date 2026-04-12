<?php

namespace App\Application\Hashtag;

use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;

class GetHashtagPostsUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
    ) {}

    /** @return Post[] */
    public function execute(string $hashtagName, ?string $authUserId = null, int $limit = 20): array
    {
        return $this->postRepository->getByHashtag($hashtagName, $authUserId, $limit);
    }
}
