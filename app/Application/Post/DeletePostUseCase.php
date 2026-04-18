<?php

namespace App\Application\Post;

use App\Domain\Post\Repositories\PostRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * 投稿を削除するユースケース。投稿者本人のみ削除を許可する。
 */
class DeletePostUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
    ) {}

    /**
     * 投稿を削除する。投稿が存在しない場合は何もしない。
     *
     * @param  string  $postId  削除対象の投稿ID
     * @param  string  $authUserId  操作を行う認証ユーザーID
     *
     * @throws AuthorizationException 投稿者本人以外が削除しようとした場合
     */
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
