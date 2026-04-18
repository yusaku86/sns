<?php

use App\Application\Post\SearchPostsUseCase;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;

function makeSearchPost(string $id, string $createdAt): Post
{
    return new Post(
        id: $id,
        userId: 'user-1',
        userName: 'テストユーザー',
        userHandle: 'test_user',
        content: "投稿{$id}",
        createdAt: new DateTimeImmutable($createdAt),
        likesCount: 0,
        likedByAuthUser: false,
    );
}

it('キーワードと認証ユーザーIDをリポジトリに渡す', function () {
    $posts = [makeSearchPost('p1', '2026-01-01T00:00:00+00:00')];

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('searchByKeyword')
        ->once()
        ->with('Laravel', 'auth-1', 21, null)
        ->andReturn($posts);

    $useCase = new SearchPostsUseCase($repository);
    $result = $useCase->execute('Laravel', 'auth-1');

    expect($result['posts'])->toHaveCount(1)
        ->and($result['hasMore'])->toBeFalse()
        ->and($result['nextCursor'])->toBeNull();
});

it('20件以下の場合はhasMore=falseを返す', function () {
    $posts = array_map(fn ($i) => makeSearchPost("p{$i}", '2026-01-01T00:00:00+00:00'), range(1, 5));

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('searchByKeyword')
        ->once()
        ->with('test', null, 21, null)
        ->andReturn($posts);

    $useCase = new SearchPostsUseCase($repository);
    $result = $useCase->execute('test');

    expect($result['posts'])->toHaveCount(5)
        ->and($result['hasMore'])->toBeFalse()
        ->and($result['nextCursor'])->toBeNull();
});

it('21件返ってきた場合はhasMore=trueで20件にトリムする', function () {
    $posts = array_map(fn ($i) => makeSearchPost("p{$i}", "2026-01-{$i}T12:00:00+00:00"), range(1, 21));

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('searchByKeyword')
        ->once()
        ->with('test', null, 21, null)
        ->andReturn($posts);

    $useCase = new SearchPostsUseCase($repository);
    $result = $useCase->execute('test');

    expect($result['posts'])->toHaveCount(20)
        ->and($result['hasMore'])->toBeTrue()
        ->and($result['nextCursor'])->not->toBeNull();
});

it('カーソルをリポジトリに渡す', function () {
    $cursor = '2026-04-14T12:00:00+00:00';
    $posts = [makeSearchPost('p1', '2026-04-13T00:00:00+00:00')];

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('searchByKeyword')
        ->once()
        ->with('test', null, 21, $cursor)
        ->andReturn($posts);

    $useCase = new SearchPostsUseCase($repository);
    $result = $useCase->execute('test', null, $cursor);

    expect($result['posts'])->toHaveCount(1)
        ->and($result['hasMore'])->toBeFalse();
});
