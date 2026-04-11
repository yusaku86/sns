<?php

use App\Domain\User\Entities\User;

it('ユーザーエンティティを生成できる', function () {
    $user = new User(
        id: 'uuid-1',
        name: 'テストユーザー',
        email: 'test@example.com',
        bio: '自己紹介文',
        postsCount: 10,
        followersCount: 5,
        followingCount: 3,
        isFollowedByAuthUser: false,
    );

    expect($user->id)->toBe('uuid-1')
        ->and($user->name)->toBe('テストユーザー')
        ->and($user->email)->toBe('test@example.com')
        ->and($user->bio)->toBe('自己紹介文')
        ->and($user->postsCount)->toBe(10)
        ->and($user->followersCount)->toBe(5)
        ->and($user->followingCount)->toBe(3)
        ->and($user->isFollowedByAuthUser)->toBeFalse();
});

it('bioはnullを許容する', function () {
    $user = new User(
        id: 'uuid-1',
        name: 'テストユーザー',
        email: 'test@example.com',
        bio: null,
        postsCount: 0,
        followersCount: 0,
        followingCount: 0,
        isFollowedByAuthUser: false,
    );

    expect($user->bio)->toBeNull();
});
