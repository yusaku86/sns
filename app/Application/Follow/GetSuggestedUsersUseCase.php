<?php

namespace App\Application\Follow;

use App\Domain\Follow\Entities\FollowUser;
use App\Domain\Follow\Repositories\FollowRepositoryInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * 認証ユーザーへのおすすめユーザー一覧を取得するユースケース。
 * 結果は30分キャッシュされ、フォロー・アンフォロー時に invalidate() で破棄される。
 */
class GetSuggestedUsersUseCase
{
    private const TTL_SECONDS = 1800;

    private const CACHE_KEY_PREFIX = 'suggested_users:';

    public function __construct(
        private FollowRepositoryInterface $followRepository,
        private CacheRepository $cache,
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
        return $this->cache->remember(
            self::CACHE_KEY_PREFIX.$authUserId,
            self::TTL_SECONDS,
            fn () => $this->followRepository->getSuggestedUsers($authUserId, $limit),
        );
    }

    /**
     * 指定ユーザーのおすすめキャッシュを破棄する。
     * フォロー・アンフォロー後に呼び出す。
     *
     * @param  string  $authUserId  キャッシュを破棄するユーザーID
     */
    public function invalidate(string $authUserId): void
    {
        $this->cache->forget(self::CACHE_KEY_PREFIX.$authUserId);
    }
}
