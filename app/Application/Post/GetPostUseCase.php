<?php

namespace App\Application\Post;

use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Reply\Entities\Reply;
use App\Domain\Reply\Repositories\ReplyRepositoryInterface;

/**
 * 投稿詳細とリプライ一覧を取得するユースケース。
 */
class GetPostUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private ReplyRepositoryInterface $replyRepository,
    ) {}

    /**
     * 投稿とそのリプライ一覧を返す。
     *
     * @param  string  $postId  投稿ID
     * @param  string|null  $authUserId  認証ユーザーID（いいね・リツイート状態の付与に使用）
     * @return array{post: Post, replies: Reply[]}
     *
     * @throws \DomainException 投稿が存在しない場合
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
