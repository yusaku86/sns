<?php

namespace App\Infrastructure\Cache;

use App\Application\Follow\SuggestedUserCacheInterface;
use App\Domain\Follow\Entities\FollowUser;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Laravel Cache を使ったおすすめユーザーキャッシュの実装。
 * データTTL: 2時間（ハード失効）
 * フレッシュネスTTL: 1時間（ステール判定）
 * FollowUser はプリミティブ配列に変換して保存し、取得時に復元する。
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
        $raw = $this->cache->get(self::KEY_PREFIX.$userId);

        if ($raw === null) {
            return null;
        }

        return array_map(fn (array $data) => new FollowUser(
            id: $data['id'],
            name: $data['name'],
            handle: $data['handle'],
            profileImageUrl: $data['profileImageUrl'],
            isFollowedByAuthUser: $data['isFollowedByAuthUser'],
        ), $raw);
    }

    /**
     * {@inheritdoc}
     *
     * @param  FollowUser[]  $users
     */
    public function put(string $userId, array $users): void
    {
        $raw = array_map(fn (FollowUser $u) => $u->jsonSerialize(), $users);

        $this->cache->put(self::KEY_PREFIX.$userId, $raw, self::DATA_TTL);
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
