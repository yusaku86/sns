<?php

use App\Application\Follow\GetSuggestedUsersUseCase;
use App\Application\Follow\SuggestedUserCacheInterface;
use App\Application\Follow\SuggestedUserRefresherInterface;
use App\Domain\Follow\Entities\FollowUser;
use App\Domain\Follow\Repositories\FollowRepositoryInterface;

it('キャッシュがなければ同期計算してキャッシュに保存する', function () {
    $users = [new FollowUser('uuid-2', '田中', 'tanaka', null, false)];

    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldReceive('getSuggestedUsers')->with('uuid-1', 5)->once()->andReturn($users);

    $cache = mock(SuggestedUserCacheInterface::class);
    $cache->shouldReceive('get')->with('uuid-1')->once()->andReturnNull();
    $cache->shouldReceive('put')->with('uuid-1', $users)->once();

    $refresher = mock(SuggestedUserRefresherInterface::class);
    $refresher->shouldNotReceive('refresh');

    $useCase = new GetSuggestedUsersUseCase($repository, $cache, $refresher);
    $result = $useCase->execute('uuid-1');

    expect($result)->toBe($users);
});

it('キャッシュがありフレッシュならそのまま返す', function () {
    $cached = [new FollowUser('uuid-2', '田中', 'tanaka', null, false)];

    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldNotReceive('getSuggestedUsers');

    $cache = mock(SuggestedUserCacheInterface::class);
    $cache->shouldReceive('get')->with('uuid-1')->once()->andReturn($cached);
    $cache->shouldReceive('isFresh')->with('uuid-1')->once()->andReturnTrue();
    $cache->shouldNotReceive('put');

    $refresher = mock(SuggestedUserRefresherInterface::class);
    $refresher->shouldNotReceive('refresh');

    $useCase = new GetSuggestedUsersUseCase($repository, $cache, $refresher);
    $result = $useCase->execute('uuid-1');

    expect($result)->toBe($cached);
});

it('キャッシュがありステールなら古いデータを返しつつバックグラウンドで再計算を依頼する', function () {
    $stale = [new FollowUser('uuid-2', '田中', 'tanaka', null, false)];

    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldNotReceive('getSuggestedUsers');

    $cache = mock(SuggestedUserCacheInterface::class);
    $cache->shouldReceive('get')->with('uuid-1')->once()->andReturn($stale);
    $cache->shouldReceive('isFresh')->with('uuid-1')->once()->andReturnFalse();
    $cache->shouldNotReceive('put');

    $refresher = mock(SuggestedUserRefresherInterface::class);
    $refresher->shouldReceive('refresh')->with('uuid-1', 5)->once();

    $useCase = new GetSuggestedUsersUseCase($repository, $cache, $refresher);
    $result = $useCase->execute('uuid-1');

    expect($result)->toBe($stale);
});
