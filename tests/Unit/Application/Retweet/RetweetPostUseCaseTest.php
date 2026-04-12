<?php

use App\Application\Retweet\RetweetPostUseCase;
use App\Application\Retweet\UnretweetPostUseCase;
use App\Domain\Retweet\Repositories\RetweetRepositoryInterface;

it('リツイートできる', function () {
    $repository = mock(RetweetRepositoryInterface::class);
    $repository->shouldReceive('exists')->with('uuid-user-1', 'uuid-post-1')->andReturn(false);
    $repository->shouldReceive('save')->with('uuid-user-1', 'uuid-post-1')->once();

    $useCase = new RetweetPostUseCase($repository);
    $useCase->execute('uuid-user-1', 'uuid-post-1');
});

it('すでにリツイート済みの場合は重複して保存しない', function () {
    $repository = mock(RetweetRepositoryInterface::class);
    $repository->shouldReceive('exists')->with('uuid-user-1', 'uuid-post-1')->andReturn(true);
    $repository->shouldNotReceive('save');

    $useCase = new RetweetPostUseCase($repository);
    $useCase->execute('uuid-user-1', 'uuid-post-1');
});

it('リツイートを取り消せる', function () {
    $repository = mock(RetweetRepositoryInterface::class);
    $repository->shouldReceive('delete')->with('uuid-user-1', 'uuid-post-1')->once();

    $useCase = new UnretweetPostUseCase($repository);
    $useCase->execute('uuid-user-1', 'uuid-post-1');
});
