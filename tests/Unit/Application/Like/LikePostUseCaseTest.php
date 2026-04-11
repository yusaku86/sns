<?php

use App\Application\Like\LikePostUseCase;
use App\Domain\Like\Repositories\LikeRepositoryInterface;

it('いいねできる', function () {
    $repository = mock(LikeRepositoryInterface::class);
    $repository->shouldReceive('exists')->with('uuid-user-1', 'uuid-post-1')->andReturn(false);
    $repository->shouldReceive('save')->with('uuid-user-1', 'uuid-post-1')->once();

    $useCase = new LikePostUseCase($repository);
    $useCase->execute('uuid-user-1', 'uuid-post-1');
});

it('すでにいいね済みの場合は重複して保存しない', function () {
    $repository = mock(LikeRepositoryInterface::class);
    $repository->shouldReceive('exists')->with('uuid-user-1', 'uuid-post-1')->andReturn(true);
    $repository->shouldNotReceive('save');

    $useCase = new LikePostUseCase($repository);
    $useCase->execute('uuid-user-1', 'uuid-post-1');
});
