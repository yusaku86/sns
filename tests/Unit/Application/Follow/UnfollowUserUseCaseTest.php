<?php

use App\Application\Follow\GetSuggestedUsersUseCase;
use App\Application\Follow\UnfollowUserUseCase;
use App\Domain\Follow\Repositories\FollowRepositoryInterface;

it('アンフォローできる', function () {
    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldReceive('delete')->with('uuid-user-1', 'uuid-user-2')->once();

    $suggestedUsers = mock(GetSuggestedUsersUseCase::class);
    $suggestedUsers->shouldReceive('invalidate')->with('uuid-user-1')->once();

    $useCase = new UnfollowUserUseCase($repository, $suggestedUsers);
    $useCase->execute('uuid-user-1', 'uuid-user-2');
});
