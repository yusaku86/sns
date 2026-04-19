<?php

use App\Application\Follow\ComputeSuggestedUsersUseCase;
use App\Application\Follow\SuggestedUserCacheInterface;
use App\Domain\Follow\Entities\FollowUser;
use App\Domain\Follow\Repositories\FollowRepositoryInterface;

it('おすすめユーザーを計算してキャッシュに保存する', function () {
    $users = [new FollowUser('uuid-2', '田中', 'tanaka', null, false)];

    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldReceive('getSuggestedUsers')->with('uuid-1', 5)->once()->andReturn($users);

    $cache = mock(SuggestedUserCacheInterface::class);
    $cache->shouldReceive('put')->with('uuid-1', $users)->once();

    $useCase = new ComputeSuggestedUsersUseCase($repository, $cache);
    $useCase->execute('uuid-1');
});

it('件数を指定してリポジトリに渡す', function () {
    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldReceive('getSuggestedUsers')->with('uuid-1', 3)->once()->andReturn([]);

    $cache = mock(SuggestedUserCacheInterface::class);
    $cache->shouldReceive('put')->once();

    $useCase = new ComputeSuggestedUsersUseCase($repository, $cache);
    $useCase->execute('uuid-1', 3);
});
