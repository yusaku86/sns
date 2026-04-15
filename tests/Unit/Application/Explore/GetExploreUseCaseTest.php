<?php

use App\Application\Explore\GetExploreUseCase;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;

function makeExplorePost(string $id, string $createdAt, ?string $retweetedAt = null): Post
{
    return new Post(
        id: $id,
        userId: 'user-1',
        userName: 'テストユーザー',
        userHandle: 'test_user',
        content: '投稿'.$id,
        createdAt: new DateTimeImmutable($createdAt),
        likesCount: 0,
        likedByAuthUser: false,
        retweetedAt: $retweetedAt ? new DateTimeImmutable($retweetedAt) : null,
    );
}

it('カーソルなし・20件以下の場合はhasMore=falseを返す', function () {
    $posts = array_map(fn ($i) => makeExplorePost("p{$i}", '2026-01-01 00:00:00'), range(1, 5));

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('getAll')
        ->once()
        ->with(null, 21, null)
        ->andReturn($posts);

    $useCase = new GetExploreUseCase($repository);
    $result = $useCase->execute();

    expect($result['posts'])->toHaveCount(5)
        ->and($result['hasMore'])->toBeFalse()
        ->and($result['nextCursor'])->toBeNull();
});

it('21件返ってきた場合はhasMore=trueで20件にトリムする', function () {
    $posts = array_map(fn ($i) => makeExplorePost("p{$i}", "2026-01-{$i}T12:00:00+00:00"), range(1, 21));

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('getAll')
        ->once()
        ->with(null, 21, null)
        ->andReturn($posts);

    $useCase = new GetExploreUseCase($repository);
    $result = $useCase->execute();

    expect($result['posts'])->toHaveCount(20)
        ->and($result['hasMore'])->toBeTrue()
        ->and($result['nextCursor'])->not->toBeNull();
});

it('認証済みユーザーIDとカーソルをリポジトリに渡す', function () {
    $cursor = '2026-04-14T12:00:00+00:00';
    $posts = [makeExplorePost('p1', '2026-04-13T00:00:00+00:00')];

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('getAll')
        ->once()
        ->with('auth-user-1', 21, $cursor)
        ->andReturn($posts);

    $useCase = new GetExploreUseCase($repository);
    $result = $useCase->execute('auth-user-1', $cursor);

    expect($result['posts'])->toHaveCount(1)
        ->and($result['hasMore'])->toBeFalse();
});

it('hasMore=trueのときnextCursorはリツイートタイムスタンプを優先する', function () {
    $post20 = makeExplorePost('p20', '2026-04-01T12:00:00+00:00', '2026-04-02T08:00:00+00:00');
    $posts = array_map(fn ($i) => makeExplorePost("p{$i}", '2026-04-'.str_pad(21 - $i, 2, '0', STR_PAD_LEFT).'T12:00:00+00:00'), range(1, 19));
    $posts[] = $post20;
    $posts[] = makeExplorePost('p21', '2026-03-31T12:00:00+00:00');

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('getAll')
        ->once()
        ->with(null, 21, null)
        ->andReturn($posts);

    $useCase = new GetExploreUseCase($repository);
    $result = $useCase->execute();

    // 20件目(p20)のretweetedAt
    $expected = (new DateTimeImmutable('2026-04-02T08:00:00+00:00'))->format(DateTimeInterface::ATOM);
    expect($result['nextCursor'])->toBe($expected);
});
