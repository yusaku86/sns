<?php

namespace App\Application\Follow;

use App\Domain\Follow\Repositories\FollowRepositoryInterface;

/**
 * おすすめユーザーを計算してキャッシュに保存するユースケース。
 * GetSuggestedUsersUseCase からの同期呼び出しと ComputeSuggestedUsersJob から利用される。
 */
class ComputeSuggestedUsersUseCase
{
    public function __construct(
        private FollowRepositoryInterface $followRepository,
        private SuggestedUserCacheInterface $cache,
    ) {}

    /**
     * おすすめユーザーを計算してキャッシュに保存する。
     *
     * @param  string  $authUserId  認証ユーザーID
     * @param  int  $limit  取得件数
     */
    public function execute(string $authUserId, int $limit = 5): void
    {
        $users = $this->followRepository->getSuggestedUsers($authUserId, $limit);
        $this->cache->put($authUserId, $users);
    }
}
