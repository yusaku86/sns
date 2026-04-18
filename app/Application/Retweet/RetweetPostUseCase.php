<?php

namespace App\Application\Retweet;

use App\Domain\Retweet\Repositories\RetweetRepositoryInterface;

/**
 * 投稿をリツイートするユースケース。二重リツイートは冪等に処理する。
 */
class RetweetPostUseCase
{
    public function __construct(
        private RetweetRepositoryInterface $retweetRepository,
    ) {}

    /**
     * リツイートを作成する。
     *
     * @param  string  $userId  リツイートするユーザーID
     * @param  string  $postId  対象投稿ID
     */
    public function execute(string $userId, string $postId): void
    {
        if ($this->retweetRepository->exists($userId, $postId)) {
            return;
        }

        $this->retweetRepository->save($userId, $postId);
    }
}
