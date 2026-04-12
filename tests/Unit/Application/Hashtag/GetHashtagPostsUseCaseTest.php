<?php

use App\Application\Hashtag\GetHashtagPostsUseCase;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;

it('ハッシュタグに紐づく投稿一覧を取得できる', function () {
    $post = new Post(
        id: 'uuid-1',
        userId: 'uuid-user-1',
        userName: 'テストユーザー',
        userHandle: 'test_user',
        content: '#Laravel の投稿',
        createdAt: new DateTimeImmutable,
        likesCount: 0,
        likedByAuthUser: false,
        hashtags: ['Laravel'],
    );

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('getByHashtag')
        ->once()
        ->with('Laravel', null, 20)
        ->andReturn([$post]);

    $useCase = new GetHashtagPostsUseCase($repository);
    $result = $useCase->execute('Laravel');

    expect($result)->toHaveCount(1)
        ->and($result[0]->content)->toBe('#Laravel の投稿');
});

it('該当するハッシュタグがない場合は空配列を返す', function () {
    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('getByHashtag')
        ->once()
        ->with('存在しないタグ', null, 20)
        ->andReturn([]);

    $useCase = new GetHashtagPostsUseCase($repository);
    $result = $useCase->execute('存在しないタグ');

    expect($result)->toBeEmpty();
});
