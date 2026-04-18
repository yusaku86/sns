<?php

use App\Application\Follow\FollowUserUseCase;
use App\Application\Follow\GetSuggestedUsersUseCase;
use App\Domain\Follow\Repositories\FollowRepositoryInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

it('フォローできる', function () {
    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldReceive('exists')->with('uuid-user-1', 'uuid-user-2')->andReturn(false);
    $repository->shouldReceive('save')->with('uuid-user-1', 'uuid-user-2')->once();

    $cache = mock(CacheRepository::class);
    $suggestedUsers = mock(GetSuggestedUsersUseCase::class);
    $suggestedUsers->shouldReceive('invalidate')->with('uuid-user-1')->once();

    $useCase = new FollowUserUseCase($repository, $suggestedUsers);
    $useCase->execute('uuid-user-1', 'uuid-user-2');
});

it('すでにフォロー済みの場合は重複して保存せずキャッシュも破棄しない', function () {
    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldReceive('exists')->with('uuid-user-1', 'uuid-user-2')->andReturn(true);
    $repository->shouldNotReceive('save');

    $suggestedUsers = mock(GetSuggestedUsersUseCase::class);
    $suggestedUsers->shouldNotReceive('invalidate');

    $useCase = new FollowUserUseCase($repository, $suggestedUsers);
    $useCase->execute('uuid-user-1', 'uuid-user-2');
});

it('自分自身はフォローできない', function () {
    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldNotReceive('save');

    $suggestedUsers = mock(GetSuggestedUsersUseCase::class);
    $suggestedUsers->shouldNotReceive('invalidate');

    $useCase = new FollowUserUseCase($repository, $suggestedUsers);

    expect(fn () => $useCase->execute('uuid-user-1', 'uuid-user-1'))
        ->toThrow(InvalidArgumentException::class);
});
