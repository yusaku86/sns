<?php

namespace App\Application\Explore;

use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;

class GetExploreUseCase
{
    private const LIMIT = 20;

    public function __construct(
        private PostRepositoryInterface $postRepository,
    ) {}

    /**
     * @return array{posts: Post[], nextCursor: string|null, hasMore: bool}
     */
    public function execute(?string $authUserId = null, ?string $cursor = null): array
    {
        $posts = $this->postRepository->getAll($authUserId, self::LIMIT + 1, $cursor);

        return $this->paginate($posts);
    }

    private function paginate(array $posts): array
    {
        $hasMore = count($posts) > self::LIMIT;

        if ($hasMore) {
            array_pop($posts);
        }

        $lastPost = end($posts);
        $nextCursor = ($hasMore && $lastPost)
            ? ($lastPost->retweetedAt ?? $lastPost->createdAt)->format(\DateTimeInterface::ATOM)
            : null;

        return [
            'posts' => $posts,
            'nextCursor' => $nextCursor,
            'hasMore' => $hasMore,
        ];
    }
}
