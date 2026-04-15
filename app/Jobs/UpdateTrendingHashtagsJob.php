<?php

namespace App\Jobs;

use App\Application\Hashtag\GetTrendingHashtagsUseCase;
use App\Domain\Hashtag\Repositories\HashtagRepositoryInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateTrendingHashtagsJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * キュー内での一意性保持時間（秒）。
     * この間に同じジョブが積まれても重複実行しない。
     */
    public int $uniqueFor = 60;

    public function handle(HashtagRepositoryInterface $hashtagRepository, CacheRepository $cache): void
    {
        $trending = array_map(
            fn ($h) => ['name' => $h->name, 'postsCount' => $h->postsCount],
            $hashtagRepository->getTrending(5),
        );

        $cache->put(GetTrendingHashtagsUseCase::CACHE_KEY, $trending, GetTrendingHashtagsUseCase::CACHE_TTL);
    }
}
