<?php

namespace App\Application\Follow;

use App\Domain\Follow\Entities\FollowUser;
use App\Domain\Follow\Repositories\FollowRepositoryInterface;

/**
 * 認証ユーザーへのおすすめユーザー一覧を取得するユースケース。
 */
class GetSuggestedUsersUseCase
{
    public function __construct(
        private FollowRepositoryInterface $followRepository,
    ) {}

    /**
     * おすすめユーザーを取得する。
     *
     * @param  string  $authUserId  認証ユーザーID
     * @param  int  $limit  取得件数
     * @return FollowUser[]
     */
    public function execute(string $authUserId, int $limit = 5): array
    {
        return $this->followRepository->getSuggestedUsers($authUserId, $limit);
    }
}
