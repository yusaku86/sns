<?php

use App\Domain\Reply\Entities\Reply;

it('返信エンティティを生成できる', function () {
    $now = new DateTimeImmutable;

    $reply = new Reply(
        id: 'uuid-reply-1',
        postId: 'uuid-post-1',
        userId: 'uuid-user-1',
        userName: 'テストユーザー',
        userHandle: 'test_user',
        content: 'テスト返信内容',
        createdAt: $now,
    );

    expect($reply->id)->toBe('uuid-reply-1')
        ->and($reply->postId)->toBe('uuid-post-1')
        ->and($reply->userId)->toBe('uuid-user-1')
        ->and($reply->userName)->toBe('テストユーザー')
        ->and($reply->userHandle)->toBe('test_user')
        ->and($reply->content)->toBe('テスト返信内容')
        ->and($reply->createdAt)->toBe($now);
});

it('返信エンティティをJSON化できる', function () {
    $now = new DateTimeImmutable('2026-04-12 10:00:00');

    $reply = new Reply(
        id: 'uuid-reply-1',
        postId: 'uuid-post-1',
        userId: 'uuid-user-1',
        userName: 'テストユーザー',
        userHandle: 'test_user',
        content: 'テスト返信内容',
        createdAt: $now,
    );

    $json = $reply->jsonSerialize();

    expect($json)->toBe([
        'id' => 'uuid-reply-1',
        'postId' => 'uuid-post-1',
        'userId' => 'uuid-user-1',
        'userName' => 'テストユーザー',
        'userHandle' => 'test_user',
        'content' => 'テスト返信内容',
        'createdAt' => '2026/04/12 10:00',
        'postContent' => null,
        'postUserName' => null,
        'postUserHandle' => null,
    ]);
});
