<?php

namespace App\Application\Retweet;

use App\Domain\Retweet\Repositories\RetweetRepositoryInterface;

/**
 * リツイートを取り消すユースケース。
 */
class UnretweetPostUseCase
{
    public function __construct(
        private RetweetRepositoryInterface $retweetRepository,
    ) {}

    /**
     * リツイートを削除する。
     *
     * @param  string  $userId  リツイートを取り消すユーザーID
     * @param  string  $postId  対象投稿ID
     */
    public function execute(string $userId, string $postId): void
    {
        $this->retweetRepository->delete($userId, $postId);
    }
}
