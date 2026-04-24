<?php

use App\Application\Explore\GetExploreUseCase;
use App\Application\Shared\FeedMerger;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Retweet\Repositories\RetweetRepositoryInterface;

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

function makeExploreUseCase(array $posts, array $retweets = [], ?string $authUserId = null, ?string $cursor = null): GetExploreUseCase
{
    $postRepo = mock(PostRepositoryInterface::class);
    $postRepo->shouldReceive('getAll')
        ->with($authUserId, 21, $cursor)
        ->andReturn($posts);

    $retweetRepo = mock(RetweetRepositoryInterface::class);
    $retweetRepo->shouldReceive('getAllAsPost')
        ->with($authUserId, 21, $cursor)
        ->andReturn($retweets);

    return new GetExploreUseCase($postRepo, $retweetRepo, new FeedMerger);
}

it('カーソルなし・20件以下の場合はhasMore=falseを返す', function () {
    $posts = array_map(fn ($i) => makeExplorePost("p{$i}", '2026-01-01 00:00:00'), range(1, 5));
    $result = makeExploreUseCase($posts)->execute();

    expect($result['posts'])->toHaveCount(5)
        ->and($result['hasMore'])->toBeFalse()
        ->and($result['nextCursor'])->toBeNull();
});

it('投稿とリツイートを合わせて21件以上あるときhasMore=trueで20件にトリムする', function () {
    $posts = array_map(fn ($i) => makeExplorePost("p{$i}", "2026-01-{$i}T12:00:00+00:00"), range(1, 20));
    $retweets = [makeExplorePost('rt1', '2025-12-01T00:00:00+00:00', '2026-01-21T00:00:00+00:00')];
    $result = makeExploreUseCase($posts, $retweets)->execute();

    expect($result['posts'])->toHaveCount(20)
        ->and($result['hasMore'])->toBeTrue()
        ->and($result['nextCursor'])->not->toBeNull();
});

it('認証済みユーザーIDとカーソルをリポジトリに渡す', function () {
    $cursor = '2026-04-14T12:00:00+00:00';
    $posts = [makeExplorePost('p1', '2026-04-13T00:00:00+00:00')];
    $result = makeExploreUseCase($posts, [], 'auth-user-1', $cursor)->execute('auth-user-1', $cursor);

    expect($result['posts'])->toHaveCount(1)
        ->and($result['hasMore'])->toBeFalse();
});

it('hasMore=trueのときnextCursorはリツイートタイムスタンプを優先する', function () {
    $post20 = makeExplorePost('p20', '2026-04-01T12:00:00+00:00', '2026-04-02T08:00:00+00:00');
    $posts = array_map(fn ($i) => makeExplorePost("p{$i}", '2026-04-'.str_pad(21 - $i, 2, '0', STR_PAD_LEFT).'T12:00:00+00:00'), range(1, 19));
    $posts[] = $post20;

    $retweets = [makeExplorePost('rt1', '2026-03-31T12:00:00+00:00')];

    $result = makeExploreUseCase($posts, $retweets)->execute();

    // 20件目(p20)のretweetedAt
    $expected = (new DateTimeImmutable('2026-04-02T08:00:00+00:00'))->format(DateTimeInterface::ATOM);
    expect($result['nextCursor'])->toBe($expected);
});
