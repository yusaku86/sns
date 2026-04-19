<?php

namespace App\Infrastructure\Cache;

use App\Application\Follow\SuggestedUserCacheInterface;
use App\Domain\Follow\Entities\FollowUser;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Laravel Cache を使ったおすすめユーザーキャッシュの実装。
 * データTTL: 2時間（ハード失効）
 * フレッシュネスTTL: 1時間（ステール判定）
 */
class LaravelSuggestedUserCache implements SuggestedUserCacheInterface
{
    private const DATA_TTL = 7200;

    private const FRESH_TTL = 3600;

    private const KEY_PREFIX = 'suggested_users:';

    private const FRESH_SUFFIX = ':fresh';

    public function __construct(
        private CacheRepository $cache,
    ) {}

    /**
     * {@inheritdoc}
     *
     * @return FollowUser[]|null
     */
    public function get(string $userId): ?array
    {
        return $this->cache->get(self::KEY_PREFIX.$userId);
    }

    /**
     * {@inheritdoc}
     *
     * @param  FollowUser[]  $users
     */
    public function put(string $userId, array $users): void
    {
        $this->cache->put(self::KEY_PREFIX.$userId, $users, self::DATA_TTL);
        $this->cache->put(self::KEY_PREFIX.$userId.self::FRESH_SUFFIX, true, self::FRESH_TTL);
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh(string $userId): bool
    {
        return (bool) $this->cache->get(self::KEY_PREFIX.$userId.self::FRESH_SUFFIX);
    }
}
