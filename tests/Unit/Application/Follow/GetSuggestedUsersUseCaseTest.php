<?php

use App\Application\Follow\GetSuggestedUsersUseCase;
use App\Domain\Follow\Entities\FollowUser;
use App\Domain\Follow\Repositories\FollowRepositoryInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

it('キャッシュがなければリポジトリから取得してキャッシュに保存する', function () {
    $users = [
        new FollowUser('uuid-2', '田中', 'tanaka', null, false),
    ];

    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldReceive('getSuggestedUsers')
        ->with('uuid-1', 5)
        ->once()
        ->andReturn($users);

    $cache = mock(CacheRepository::class);
    $cache->shouldReceive('remember')
        ->with('suggested_users:uuid-1', 1800, Mockery::type('Closure'))
        ->once()
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $useCase = new GetSuggestedUsersUseCase($repository, $cache);
    $result = $useCase->execute('uuid-1');

    expect($result)->toBe($users);
});

it('キャッシュがあればリポジトリを呼ばずキャッシュを返す', function () {
    $cached = [
        new FollowUser('uuid-2', '田中', 'tanaka', null, false),
    ];

    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldNotReceive('getSuggestedUsers');

    $cache = mock(CacheRepository::class);
    $cache->shouldReceive('remember')
        ->once()
        ->andReturn($cached);

    $useCase = new GetSuggestedUsersUseCase($repository, $cache);
    $result = $useCase->execute('uuid-1');

    expect($result)->toBe($cached);
});

it('件数を指定してリポジトリに渡す', function () {
    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldReceive('getSuggestedUsers')
        ->with('uuid-1', 3)
        ->once()
        ->andReturn([]);

    $cache = mock(CacheRepository::class);
    $cache->shouldReceive('remember')
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $useCase = new GetSuggestedUsersUseCase($repository, $cache);
    $useCase->execute('uuid-1', 3);
});

it('invalidate でキャッシュを破棄する', function () {
    $repository = mock(FollowRepositoryInterface::class);

    $cache = mock(CacheRepository::class);
    $cache->shouldReceive('forget')
        ->with('suggested_users:uuid-1')
        ->once();

    $useCase = new GetSuggestedUsersUseCase($repository, $cache);
    $useCase->invalidate('uuid-1');
});
