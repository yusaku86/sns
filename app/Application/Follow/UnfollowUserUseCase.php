<?php

namespace App\Application\Follow;

use App\Domain\Follow\Repositories\FollowRepositoryInterface;

/**
 * ユーザーのフォローを解除するユースケース。
 */
class UnfollowUserUseCase
{
    public function __construct(
        private FollowRepositoryInterface $followRepository,
    ) {}

    /**
     * フォロー関係を削除する。
     *
     * @param  string  $followerId  フォローを解除するユーザーID
     * @param  string  $followingId  フォロー解除される対象ユーザーID
     */
    public function execute(string $followerId, string $followingId): void
    {
        $this->followRepository->delete($followerId, $followingId);
    }
}
