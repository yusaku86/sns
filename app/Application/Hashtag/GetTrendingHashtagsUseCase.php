<?php

namespace App\Application\Hashtag;

use App\Domain\Hashtag\Repositories\HashtagRepositoryInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class GetTrendingHashtagsUseCase
{
    public const CACHE_KEY = 'trending_hashtags';

    public const CACHE_TTL = 300;

    public function __construct(
        private HashtagRepositoryInterface $hashtagRepository,
        private CacheRepository $cache,
    ) {}

    /**
     * @return array<array{name: string, postsCount: int}>
     */
    public function execute(): array
    {
        return $this->cache->remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return array_map(
                fn ($h) => ['name' => $h->name, 'postsCount' => $h->postsCount],
                $this->hashtagRepository->getTrending(5),
            );
        });
    }
}
