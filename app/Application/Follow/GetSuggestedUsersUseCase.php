<?php

namespace App\Application\Follow;

use App\Domain\Follow\Entities\FollowUser;
use App\Domain\Follow\Repositories\FollowRepositoryInterface;

/**
 * おすすめユーザー一覧を取得するユースケース。
 * Stale-while-revalidate 戦略でキャッシュを管理する。
 */
class GetSuggestedUsersUseCase
{
    public function __construct(
        private FollowRepositoryInterface $followRepository,
        private SuggestedUserCacheInterface $cache,
        private SuggestedUserRefresherInterface $refresher,
    ) {}

    /**
     * おすすめユーザーを返す。
     * キャッシュなし: 同期計算して保存。
     * フレッシュ: キャッシュをそのまま返す。
     * ステール: 古いキャッシュを即返しつつバックグラウンドで再計算を依頼する。
     *
     * @param  string  $authUserId  認証ユーザーID
     * @param  int  $limit  取得件数
     * @return FollowUser[]
     */
    public function execute(string $authUserId, int $limit = 5): array
    {
        $cached = $this->cache->get($authUserId);

        if ($cached === null) {
            $users = $this->followRepository->getSuggestedUsers($authUserId, $limit);
            $this->cache->put($authUserId, $users);

            return $users;
        }

        if (! $this->cache->isFresh($authUserId)) {
            $this->refresher->refresh($authUserId, $limit);
        }

        return $cached;
    }
}
