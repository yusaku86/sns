<?php

use App\Application\Post\CreatePostUseCase;
use App\Domain\Post\Repositories\PostRepositoryInterface;

it('投稿を作成できる', function () {
    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('save')->once();

    $useCase = new CreatePostUseCase($repository);
    $post = $useCase->execute('uuid-user-1', 'テストユーザー', 'テスト投稿内容');

    expect($post->userId)->toBe('uuid-user-1')
        ->and($post->userName)->toBe('テストユーザー')
        ->and($post->content)->toBe('テスト投稿内容')
        ->and($post->likesCount)->toBe(0)
        ->and($post->likedByAuthUser)->toBeFalse()
        ->and($post->id)->not->toBeEmpty();
});
