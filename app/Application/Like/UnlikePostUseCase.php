<?php

namespace App\Application\Like;

use App\Domain\Like\Repositories\LikeRepositoryInterface;

/**
 * 投稿のいいねを取り消すユースケース。
 */
class UnlikePostUseCase
{
    public function __construct(
        private LikeRepositoryInterface $likeRepository,
    ) {}

    /**
     * いいねを削除する。
     *
     * @param  string  $userId  いいねを取り消すユーザーID
     * @param  string  $postId  対象投稿ID
     */
    public function execute(string $userId, string $postId): void
    {
        $this->likeRepository->delete($userId, $postId);
    }
}
