<?php

namespace App\Application\Follow;

use App\Domain\Follow\Repositories\FollowRepositoryInterface;
use InvalidArgumentException;

/**
 * ユーザーをフォローするユースケース。二重フォローは冪等に処理する。
 */
class FollowUserUseCase
{
    public function __construct(
        private FollowRepositoryInterface $followRepository,
        private GetSuggestedUsersUseCase $suggestedUsers,
    ) {}

    /**
     * フォロー関係を作成する。
     *
     * @param  string  $followerId  フォローするユーザーID
     * @param  string  $followingId  フォローされるユーザーID
     *
     * @throws InvalidArgumentException 自分自身をフォローしようとした場合
     */
    public function execute(string $followerId, string $followingId): void
    {
        if ($followerId === $followingId) {
            throw new InvalidArgumentException('自分自身をフォローすることはできません。');
        }

        // 既にフォロー済みの場合は状態変更なし・キャッシュも維持
        if ($this->followRepository->exists($followerId, $followingId)) {
            return;
        }

        $this->followRepository->save($followerId, $followingId);
        $this->suggestedUsers->invalidate($followerId);
    }
}
