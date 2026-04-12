<?php

namespace App\Application\Reply;

use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Reply\Entities\Reply;
use App\Domain\Reply\Repositories\ReplyRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Str;

class CreateReplyUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private ReplyRepositoryInterface $replyRepository,
    ) {}

    public function execute(string $postId, string $userId, string $userName, string $userHandle, string $content): Reply
    {
        if (! $this->postRepository->findById($postId)) {
            throw new \DomainException('Post not found.');
        }

        $reply = new Reply(
            id: (string) Str::uuid(),
            postId: $postId,
            userId: $userId,
            userName: $userName,
            userHandle: $userHandle,
            content: $content,
            createdAt: new DateTimeImmutable,
        );

        $this->replyRepository->save($reply);

        return $reply;
    }
}
