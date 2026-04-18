<?php

namespace App\Application\Like;

use App\Domain\Like\Repositories\LikeRepositoryInterface;

/**
 * 投稿にいいねするユースケース。二重いいねは冪等に処理する。
 */
class LikePostUseCase
{
    public function __construct(
        private LikeRepositoryInterface $likeRepository,
    ) {}

    /**
     * いいねを作成する。
     *
     * @param  string  $userId  いいねするユーザーID
     * @param  string  $postId  対象投稿ID
     */
    public function execute(string $userId, string $postId): void
    {
        if ($this->likeRepository->exists($userId, $postId)) {
            return;
        }

        $this->likeRepository->save($userId, $postId);
    }
}
