<?php

namespace App\Application\Hashtag;

use App\Domain\Hashtag\Entities\Hashtag;
use App\Domain\Hashtag\Repositories\HashtagRepositoryInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * トレンドハッシュタグ一覧をキャッシュ付きで取得するユースケース。
 */
class GetTrendingHashtagsUseCase
{
    /** @var string キャッシュキー */
    public const CACHE_KEY = 'trending_hashtags';

    /** @var int キャッシュTTL（秒） */
    public const CACHE_TTL = 300;

    /** @var int 取得件数上限 */
    public const LIMIT = 5;

    public function __construct(
        private HashtagRepositoryInterface $hashtagRepository,
        private CacheRepository $cache,
    ) {}

    /**
     * トレンドハッシュタグ一覧をキャッシュから返す。
     *
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
     * Jobなど非同期処理からキャッシュを再構築する際に使用する。
     *
     * @return Hashtag[]
     */
    public function refresh(): array
    {
        $this->cache->forget(self::CACHE_KEY);

        return $this->execute();
    }
}
