<?php

use App\Application\Follow\FollowUserUseCase;
use App\Domain\Follow\Repositories\FollowRepositoryInterface;

it('フォローできる', function () {
    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldReceive('exists')->with('uuid-user-1', 'uuid-user-2')->andReturn(false);
    $repository->shouldReceive('save')->with('uuid-user-1', 'uuid-user-2')->once();

    $useCase = new FollowUserUseCase($repository);
    $useCase->execute('uuid-user-1', 'uuid-user-2');
});

it('すでにフォロー済みの場合は重複して保存しない', function () {
    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldReceive('exists')->with('uuid-user-1', 'uuid-user-2')->andReturn(true);
    $repository->shouldNotReceive('save');

    $useCase = new FollowUserUseCase($repository);
    $useCase->execute('uuid-user-1', 'uuid-user-2');
});

it('自分自身はフォローできない', function () {
    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldNotReceive('save');

    $useCase = new FollowUserUseCase($repository);

    expect(fn () => $useCase->execute('uuid-user-1', 'uuid-user-1'))
        ->toThrow(InvalidArgumentException::class);
});
