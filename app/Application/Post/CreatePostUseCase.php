<?php

namespace App\Application\Post;

use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Str;

class CreatePostUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
    ) {}

    public function execute(string $userId, string $userName, string $content): Post
    {
        $post = new Post(
            id: (string) Str::uuid(),
            userId: $userId,
            userName: $userName,
            content: $content,
            createdAt: new DateTimeImmutable,
            likesCount: 0,
            likedByAuthUser: false,
        );

        $this->postRepository->save($post);

        return $post;
    }
}
