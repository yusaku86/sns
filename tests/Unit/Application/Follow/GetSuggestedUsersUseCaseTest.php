<?php

use App\Application\Follow\GetSuggestedUsersUseCase;
use App\Domain\Follow\Entities\FollowUser;
use App\Domain\Follow\Repositories\FollowRepositoryInterface;

it('おすすめユーザーをリポジトリから取得して返す', function () {
    $users = [
        new FollowUser('uuid-2', '田中', 'tanaka', null, false),
        new FollowUser('uuid-3', '鈴木', 'suzuki', null, false),
    ];

    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldReceive('getSuggestedUsers')
        ->with('uuid-1', 5)
        ->once()
        ->andReturn($users);

    $useCase = new GetSuggestedUsersUseCase($repository);
    $result = $useCase->execute('uuid-1');

    expect($result)->toBe($users);
});

it('件数を指定してリポジトリに渡す', function () {
    $repository = mock(FollowRepositoryInterface::class);
    $repository->shouldReceive('getSuggestedUsers')
        ->with('uuid-1', 3)
        ->once()
        ->andReturn([]);

    $useCase = new GetSuggestedUsersUseCase($repository);
    $useCase->execute('uuid-1', 3);
});
