<?php

use App\Application\Hashtag\GetTrendingHashtagsUseCase;
use App\Domain\Hashtag\Entities\Hashtag;
use App\Domain\Hashtag\Repositories\HashtagRepositoryInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

it('キャッシュがない場合はリポジトリから取得してキャッシュに保存する', function () {
    $hashtags = [
        new Hashtag(id: 'uuid-1', name: 'Laravel', postsCount: 100),
        new Hashtag(id: 'uuid-2', name: 'PHP', postsCount: 80),
    ];

    $repository = mock(HashtagRepositoryInterface::class);
    $repository->shouldReceive('getTrending')
        ->once()
        ->with(GetTrendingHashtagsUseCase::LIMIT)
        ->andReturn($hashtags);

    $cache = mock(CacheRepository::class);
    $cache->shouldReceive('remember')
        ->once()
        ->withArgs(fn ($key, $ttl, $callback) => $key === 'trending_hashtags' && $ttl === 300)
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $useCase = new GetTrendingHashtagsUseCase($repository, $cache);
    $result = $useCase->execute();

    expect($result)->toHaveCount(2)
        ->and($result[0])->toBeInstanceOf(Hashtag::class)
        ->and($result[0]->name)->toBe('Laravel')
        ->and($result[0]->postsCount)->toBe(100)
        ->and($result[1]->name)->toBe('PHP')
        ->and($result[1]->postsCount)->toBe(80);
});

it('キャッシュがある場合はリポジトリを呼ばずにキャッシュから返す', function () {
    $cached = [
        new Hashtag(id: 'uuid-1', name: 'Laravel', postsCount: 100),
        new Hashtag(id: 'uuid-2', name: 'PHP', postsCount: 80),
    ];

    $repository = mock(HashtagRepositoryInterface::class);
    $repository->shouldNotReceive('getTrending');

    $cache = mock(CacheRepository::class);
    $cache->shouldReceive('remember')
        ->once()
        ->andReturn($cached);

    $useCase = new GetTrendingHashtagsUseCase($repository, $cache);
    $result = $useCase->execute();

    expect($result)->toHaveCount(2)
        ->and($result[0])->toBeInstanceOf(Hashtag::class)
        ->and($result[0]->name)->toBe('Laravel');
});

it('refresh はキャッシュを破棄してリポジトリから再取得する', function () {
    $hashtags = [new Hashtag(id: 'uuid-1', name: 'Laravel', postsCount: 10)];

    $repository = mock(HashtagRepositoryInterface::class);
    $repository->shouldReceive('getTrending')
        ->once()
        ->with(GetTrendingHashtagsUseCase::LIMIT)
        ->andReturn($hashtags);

    $cache = mock(CacheRepository::class);
    $cache->shouldReceive('forget')
        ->once()
        ->with(GetTrendingHashtagsUseCase::CACHE_KEY);
    $cache->shouldReceive('remember')
        ->once()
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $useCase = new GetTrendingHashtagsUseCase($repository, $cache);
    $result = $useCase->refresh();

    expect($result)->toHaveCount(1)
        ->and($result[0]->name)->toBe('Laravel');
});

it('ハッシュタグが0件の場合は空配列を返す', function () {
    $repository = mock(HashtagRepositoryInterface::class);
    $repository->shouldReceive('getTrending')
        ->once()
        ->with(5)
        ->andReturn([]);

    $cache = mock(CacheRepository::class);
    $cache->shouldReceive('remember')
        ->once()
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $useCase = new GetTrendingHashtagsUseCase($repository, $cache);
    $result = $useCase->execute();

    expect($result)->toBeEmpty();
});
