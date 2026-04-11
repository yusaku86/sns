<?php

use App\Domain\Post\Entities\Post;

it('投稿エンティティを生成できる', function () {
    $now = new DateTimeImmutable;

    $post = new Post(
        id: 'uuid-1',
        userId: 'uuid-user-1',
        userName: 'テストユーザー',
        content: 'テスト投稿内容',
        createdAt: $now,
        likesCount: 3,
        likedByAuthUser: false,
    );

    expect($post->id)->toBe('uuid-1')
        ->and($post->userId)->toBe('uuid-user-1')
        ->and($post->userName)->toBe('テストユーザー')
        ->and($post->content)->toBe('テスト投稿内容')
        ->and($post->createdAt)->toBe($now)
        ->and($post->likesCount)->toBe(3)
        ->and($post->likedByAuthUser)->toBeFalse();
});

it('いいね済みフラグがtrueの投稿エンティティを生成できる', function () {
    $post = new Post(
        id: 'uuid-1',
        userId: 'uuid-user-1',
        userName: 'テストユーザー',
        content: 'テスト投稿内容',
        createdAt: new DateTimeImmutable,
        likesCount: 1,
        likedByAuthUser: true,
    );

    expect($post->likedByAuthUser)->toBeTrue()
        ->and($post->likesCount)->toBe(1);
});
