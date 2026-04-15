<?php

namespace App\Application\Hashtag;

use App\Domain\Hashtag\Entities\Hashtag;
use App\Domain\Hashtag\Repositories\HashtagRepositoryInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class GetTrendingHashtagsUseCase
{
    public const CACHE_KEY = 'trending_hashtags';

    public const CACHE_TTL = 300;

    public const LIMIT = 5;

    public function __construct(
        private HashtagRepositoryInterface $hashtagRepository,
        private CacheRepository $cache,
    ) {}

    /**
     * @return Hashtag[]
     */
    public function execute(): array
    {
        return $this->cache->remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->hashtagRepository->getTrending(self::LIMIT);
        });
    }

    /**
     * キャッシュを破棄してリポジトリから再取得する。
     * Job など非同期処理からキャッシュを再構築する際に使用する。
     *
     * @return Hashtag[]
     */
    public function refresh(): array
    {
        $this->cache->forget(self::CACHE_KEY);

        return $this->execute();
    }
}
