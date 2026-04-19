<?php

use App\Application\Follow\UnfollowUserUseCase;
use App\Domain\Follow\Repositories\FollowRepositoryInterface;

it('アンフォローできる', function () {
    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldReceive('delete')->with('uuid-user-1', 'uuid-user-2')->once();

    $useCase = new UnfollowUserUseCase($repository);
    $useCase->execute('uuid-user-1', 'uuid-user-2');
});
