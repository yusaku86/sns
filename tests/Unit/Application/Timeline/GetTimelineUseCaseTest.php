<?php

use App\Application\Timeline\GetTimelineUseCase;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;

function makePost(string $id, string $createdAt, ?string $retweetedAt = null): Post
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
    $posts = array_map(fn ($i) => makePost("p{$i}", '2026-01-01 00:00:00'), range(1, 5));

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('getTimeline')
        ->once()
        ->with('user-1', 21, null)
        ->andReturn($posts);

    $useCase = new GetTimelineUseCase($repository);
    $result = $useCase->execute('user-1');

    expect($result['posts'])->toHaveCount(5)
        ->and($result['hasMore'])->toBeFalse()
        ->and($result['nextCursor'])->toBeNull();
});

it('21件返ってきた場合はhasMore=trueで20件にトリムする', function () {
    $posts = array_map(fn ($i) => makePost("p{$i}", "2026-01-{$i}T12:00:00+00:00"), range(1, 21));

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('getTimeline')
        ->once()
        ->with('user-1', 21, null)
        ->andReturn($posts);

    $useCase = new GetTimelineUseCase($repository);
    $result = $useCase->execute('user-1');

    expect($result['posts'])->toHaveCount(20)
        ->and($result['hasMore'])->toBeTrue()
        ->and($result['nextCursor'])->not->toBeNull();
});

it('nextCursorは最後の投稿の表示タイムスタンプ（retweetedAt ?? createdAt）のISO8601文字列', function () {
    $posts = [
        makePost('p1', '2026-04-14T12:00:00+00:00'),
        makePost('p2', '2026-04-13T12:00:00+00:00', '2026-04-14T10:00:00+00:00'),
        makePost('p3', '2026-04-12T12:00:00+00:00'),
    ];

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('getTimeline')
        ->once()
        ->with('user-1', 21, null)
        ->andReturn($posts);

    $useCase = new GetTimelineUseCase($repository);
    $result = $useCase->execute('user-1');

    // 最後の投稿(p3)のcreatedAt
    $expectedCursor = (new DateTimeImmutable('2026-04-12T12:00:00+00:00'))->format(DateTimeInterface::ATOM);
    expect($result['nextCursor'])->toBeNull(); // hasMore=false なので null
});

it('カーソルありの場合はリポジトリにカーソルを渡す', function () {
    $cursor = '2026-04-14T12:00:00+00:00';
    $posts = [makePost('p1', '2026-04-13T00:00:00+00:00')];

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('getTimeline')
        ->once()
        ->with('user-1', 21, $cursor)
        ->andReturn($posts);

    $useCase = new GetTimelineUseCase($repository);
    $result = $useCase->execute('user-1', $cursor);

    expect($result['posts'])->toHaveCount(1)
        ->and($result['hasMore'])->toBeFalse();
});

it('hasMore=trueのときnextCursorは20件目の表示タイムスタンプ', function () {
    $posts = array_map(fn ($i) => makePost("p{$i}", '2026-04-'.str_pad(21 - $i, 2, '0', STR_PAD_LEFT).'T12:00:00+00:00'), range(1, 21));

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('getTimeline')
        ->once()
        ->with('user-1', 21, null)
        ->andReturn($posts);

    $useCase = new GetTimelineUseCase($repository);
    $result = $useCase->execute('user-1');

    // 20件目(インデックス19)のcreatedAt
    $expected = $posts[19]->createdAt->format(DateTimeInterface::ATOM);
    expect($result['nextCursor'])->toBe($expected);
});
