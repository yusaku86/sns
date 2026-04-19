<?php

namespace App\Application\Follow;

use App\Domain\Follow\Entities\FollowUser;
use App\Domain\Follow\Repositories\FollowRepositoryInterface;

/**
 * おすすめユーザー一覧を取得するユースケース。
 * Stale-while-revalidate 戦略でキャッシュを管理する。
 * ユーザーリストはキャッシュから返すが、isFollowedByAuthUser は毎回 DB から取得する。
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
     * いずれの場合も isFollowedByAuthUser は DB から最新値を取得して付与する。
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
            if (! empty($users)) {
                $this->cache->put($authUserId, $users);
            }
        } else {
            if (! $this->cache->isFresh($authUserId)) {
                $this->refresher->refresh($authUserId, $limit);
            }
            $users = $cached;
        }

        return $this->withFreshFollowStatus($authUserId, $users);
    }

    /**
     * ユーザーリストの isFollowedByAuthUser を DB から取得した最新値で上書きして返す。
     *
     * @param  string  $authUserId  認証ユーザーID
     * @param  FollowUser[]  $users  対象ユーザーリスト
     * @return FollowUser[]
     */
    private function withFreshFollowStatus(string $authUserId, array $users): array
    {
        if (empty($users)) {
            return [];
        }

        $userIds = array_map(fn (FollowUser $u) => $u->id, $users);
        $followingIds = array_flip($this->followRepository->getFollowingIds($authUserId, $userIds));

        return array_map(fn (FollowUser $u) => new FollowUser(
            id: $u->id,
            name: $u->name,
            handle: $u->handle,
            profileImageUrl: $u->profileImageUrl,
            isFollowedByAuthUser: isset($followingIds[$u->id]),
        ), $users);
    }
}
