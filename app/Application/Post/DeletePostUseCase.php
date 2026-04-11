<?php

namespace App\Application\Post;

use App\Domain\Post\Repositories\PostRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;

class DeletePostUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
    ) {}

    public function execute(string $postId, string $authUserId): void
    {
        $post = $this->postRepository->findById($postId);

        if (! $post) {
            return;
        }

        if ($post->userId !== $authUserId) {
            throw new AuthorizationException('他のユーザーの投稿は削除できません。');
        }

        $this->postRepository->delete($postId);
    }
}
