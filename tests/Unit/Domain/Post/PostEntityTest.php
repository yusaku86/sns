<?php

use App\Domain\Post\Entities\Post;

it('投稿エンティティを生成できる', function () {
    $now = new DateTimeImmutable;

    $post = new Post(
        id: 'uuid-1',
        userId: 'uuid-user-1',
        userName: 'テストユーザー',
        userHandle: 'test_user',
        content: 'テスト投稿内容',
        createdAt: $now,
        likesCount: 3,
        likedByAuthUser: false,
    );

    expect($post->id)->toBe('uuid-1')
        ->and($post->userId)->toBe('uuid-user-1')
        ->and($post->userName)->toBe('テストユーザー')
        ->and($post->userHandle)->toBe('test_user')
        ->and($post->content)->toBe('テスト投稿内容')
        ->and($post->createdAt)->toBe($now)
        ->and($post->likesCount)->toBe(3)
        ->and($post->likedByAuthUser)->toBeFalse()
        ->and($post->repliesCount)->toBe(0);
});

it('リツイート情報付きの投稿エンティティを生成できる', function () {
    $now = new DateTimeImmutable;
    $retweetedAt = new DateTimeImmutable('2026-04-12 10:00:00');

    $post = new Post(
        id: 'uuid-1',
        userId: 'uuid-user-1',
        userName: '投稿者',
        userHandle: 'poster',
        content: '元の投稿',
        createdAt: $now,
        likesCount: 0,
        likedByAuthUser: false,
        retweetId: 'uuid-rt-1',
        retweetedByUserName: 'リツイーター',
        retweetedByUserHandle: 'retweeter',
        retweetedAt: $retweetedAt,
    );

    expect($post->retweetId)->toBe('uuid-rt-1')
        ->and($post->retweetedByUserName)->toBe('リツイーター')
        ->and($post->retweetedByUserHandle)->toBe('retweeter')
        ->and($post->retweetedAt)->toBe($retweetedAt);

    $json = $post->jsonSerialize();
    expect($json['retweetId'])->toBe('uuid-rt-1')
        ->and($json['retweetedByUserName'])->toBe('リツイーター')
        ->and($json['retweetedByUserHandle'])->toBe('retweeter');
});

it('いいね済みフラグがtrueの投稿エンティティを生成できる', function () {
    $post = new Post(
        id: 'uuid-1',
        userId: 'uuid-user-1',
        userName: 'テストユーザー',
        userHandle: 'test_user',
        content: 'テスト投稿内容',
        createdAt: new DateTimeImmutable,
        likesCount: 1,
        likedByAuthUser: true,
    );

    expect($post->likedByAuthUser)->toBeTrue()
        ->and($post->likesCount)->toBe(1);
});
