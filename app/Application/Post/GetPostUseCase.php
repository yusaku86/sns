<?php

namespace App\Application\Post;

use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Reply\Entities\Reply;
use App\Domain\Reply\Repositories\ReplyRepositoryInterface;

class GetPostUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private ReplyRepositoryInterface $replyRepository,
    ) {}

    /**
     * @return array{post: Post, replies: Reply[]}
     */
    public function execute(string $postId, ?string $authUserId = null): array
    {
        $post = $this->postRepository->findById($postId, $authUserId);

        if (! $post) {
            throw new \DomainException('Post not found.');
        }

        $replies = $this->replyRepository->getByPostId($postId);

        return ['post' => $post, 'replies' => $replies];
    }
}
