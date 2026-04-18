<?php

namespace App\Application\Reply;

use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Reply\Entities\Reply;
use App\Domain\Reply\Repositories\ReplyRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Str;

/**
 * 投稿へのリプライを新規作成するユースケース。
 */
class CreateReplyUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private ReplyRepositoryInterface $replyRepository,
    ) {}

    /**
     * リプライを作成して返す。
     *
     * @param  string  $postId  リプライ先の投稿ID
     * @param  string  $userId  リプライ投稿者のユーザーID
     * @param  string  $userName  リプライ投稿者の表示名
     * @param  string  $userHandle  リプライ投稿者のハンドル名
     * @param  string  $content  リプライ本文
     * @return Reply 作成されたリプライエンティティ
     *
     * @throws \DomainException リプライ先の投稿が存在しない場合
     */
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
