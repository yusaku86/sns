<?php

use App\Application\Shared\FeedMerger;
use App\Application\Timeline\GetTimelineUseCase;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Retweet\Repositories\RetweetRepositoryInterface;

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

function makeTimelineUseCase(array $posts, array $retweets = [], ?string $cursor = null): GetTimelineUseCase
{
    $postRepo = mock(PostRepositoryInterface::class);
    $postRepo->shouldReceive('getTimeline')
        ->with('user-1', 21, $cursor)
        ->andReturn($posts);

    $retweetRepo = mock(RetweetRepositoryInterface::class);
    $retweetRepo->shouldReceive('getForTimeline')
        ->with('user-1', 'user-1', 21, $cursor)
        ->andReturn($retweets);

    return new GetTimelineUseCase($postRepo, $retweetRepo, new FeedMerger);
}

it('カーソルなし・20件以下の場合はhasMore=falseを返す', function () {
    $posts = array_map(fn ($i) => makePost("p{$i}", '2026-01-01 00:00:00'), range(1, 5));
    $result = makeTimelineUseCase($posts)->execute('user-1');

    expect($result['posts'])->toHaveCount(5)
        ->and($result['hasMore'])->toBeFalse()
        ->and($result['nextCursor'])->toBeNull();
});

it('投稿とリツイートを合わせて21件以上あるときhasMore=trueで20件にトリムする', function () {
    $posts = array_map(fn ($i) => makePost("p{$i}", "2026-01-{$i}T12:00:00+00:00"), range(1, 20));
    $retweets = [makePost('rt1', '2025-12-01T00:00:00+00:00', '2026-01-21T00:00:00+00:00')];
    $result = makeTimelineUseCase($posts, $retweets)->execute('user-1');

    expect($result['posts'])->toHaveCount(20)
        ->and($result['hasMore'])->toBeTrue()
        ->and($result['nextCursor'])->not->toBeNull();
});

it('nextCursorは最後の投稿の表示タイムスタンプ（retweetedAt ?? createdAt）のISO8601文字列', function () {
    $posts = [
        makePost('p1', '2026-04-14T12:00:00+00:00'),
        makePost('p2', '2026-04-13T12:00:00+00:00'),
        makePost('p3', '2026-04-12T12:00:00+00:00'),
    ];
    $result = makeTimelineUseCase($posts)->execute('user-1');

    // hasMore=false なので null
    expect($result['nextCursor'])->toBeNull();
});

it('カーソルありの場合はリポジトリにカーソルを渡す', function () {
    $cursor = '2026-04-14T12:00:00+00:00';
    $posts = [makePost('p1', '2026-04-13T00:00:00+00:00')];
    $result = makeTimelineUseCase($posts, [], $cursor)->execute('user-1', $cursor);

    expect($result['posts'])->toHaveCount(1)
        ->and($result['hasMore'])->toBeFalse();
});

it('hasMore=trueのときnextCursorはリツイートタイムスタンプを優先する', function () {
    // 20件分の投稿（新しい順）+ リツイート1件（最古のものより新しいretweetedAt）
    $posts = array_map(
        fn ($i) => makePost("p{$i}", '2026-04-'.str_pad(21 - $i, 2, '0', STR_PAD_LEFT).'T12:00:00+00:00'),
        range(1, 20),
    );
    // 20件目（最古）の投稿と同日だが retweetedAt が設定されたリツイート
    $rt = makePost('rt1', '2026-03-01T00:00:00+00:00', '2026-04-01T08:00:00+00:00');
    $retweets = [$rt];

    $result = makeTimelineUseCase($posts, $retweets)->execute('user-1');

    // merged後: p1〜p20 + rt1 = 21件 → hasMore=true, 20件に切り詰め
    // 20件目は p20(2026-04-01T12:00:00) と rt1(retweetedAt=2026-04-01T08:00:00) のどちらか古い方
    expect($result['hasMore'])->toBeTrue()
        ->and($result['nextCursor'])->not->toBeNull();
});
