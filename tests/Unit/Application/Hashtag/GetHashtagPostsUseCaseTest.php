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
        ->with('Laravel', null, 21, null)
        ->andReturn([$post]);

    $useCase = new GetHashtagPostsUseCase($repository);
    $result = $useCase->execute('Laravel');

    expect($result['posts'])->toHaveCount(1)
        ->and($result['posts'][0]->content)->toBe('#Laravel の投稿')
        ->and($result['hasMore'])->toBeFalse()
        ->and($result['nextCursor'])->toBeNull();
});

it('該当するハッシュタグがない場合は空配列を返す', function () {
    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('getByHashtag')
        ->once()
        ->with('存在しないタグ', null, 21, null)
        ->andReturn([]);

    $useCase = new GetHashtagPostsUseCase($repository);
    $result = $useCase->execute('存在しないタグ');

    expect($result['posts'])->toBeEmpty()
        ->and($result['hasMore'])->toBeFalse()
        ->and($result['nextCursor'])->toBeNull();
});

it('21件返ってきた場合はhasMore=trueで20件にトリムする', function () {
    $posts = array_map(fn ($i) => new Post(
        id: "p{$i}",
        userId: 'user-1',
        userName: 'ユーザー',
        userHandle: 'user',
        content: "投稿{$i}",
        createdAt: new DateTimeImmutable("2026-04-{$i}T12:00:00+00:00"),
        likesCount: 0,
        likedByAuthUser: false,
    ), range(1, 21));

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('getByHashtag')
        ->once()
        ->with('Laravel', null, 21, null)
        ->andReturn($posts);

    $useCase = new GetHashtagPostsUseCase($repository);
    $result = $useCase->execute('Laravel');

    expect($result['posts'])->toHaveCount(20)
        ->and($result['hasMore'])->toBeTrue()
        ->and($result['nextCursor'])->not->toBeNull();
});

it('カーソルあり・認証ユーザーありの場合はリポジトリに渡す', function () {
    $cursor = '2026-04-14T12:00:00+00:00';
    $post = new Post(
        id: 'p1',
        userId: 'user-1',
        userName: 'ユーザー',
        userHandle: 'user',
        content: '投稿',
        createdAt: new DateTimeImmutable('2026-04-13T00:00:00+00:00'),
        likesCount: 0,
        likedByAuthUser: false,
    );

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('getByHashtag')
        ->once()
        ->with('Laravel', 'auth-user-1', 21, $cursor)
        ->andReturn([$post]);

    $useCase = new GetHashtagPostsUseCase($repository);
    $result = $useCase->execute('Laravel', 'auth-user-1', $cursor);

    expect($result['posts'])->toHaveCount(1)
        ->and($result['hasMore'])->toBeFalse();
});
