<?php

use App\Domain\User\Entities\User;

it('ユーザーエンティティを生成できる', function () {
    $createdAt = new DateTimeImmutable('2024-03-01');

    $user = new User(
        id: 'uuid-1',
        name: 'テストユーザー',
        handle: 'test_user',
        email: 'test@example.com',
        bio: '自己紹介文',
        headerImageUrl: 'https://example.com/header.jpg',
        profileImageUrl: 'https://example.com/profile.jpg',
        postsCount: 10,
        followersCount: 5,
        followingCount: 3,
        isFollowedByAuthUser: false,
        createdAt: $createdAt,
    );

    expect($user->id)->toBe('uuid-1')
        ->and($user->name)->toBe('テストユーザー')
        ->and($user->handle)->toBe('test_user')
        ->and($user->email)->toBe('test@example.com')
        ->and($user->bio)->toBe('自己紹介文')
        ->and($user->headerImageUrl)->toBe('https://example.com/header.jpg')
        ->and($user->profileImageUrl)->toBe('https://example.com/profile.jpg')
        ->and($user->postsCount)->toBe(10)
        ->and($user->followersCount)->toBe(5)
        ->and($user->followingCount)->toBe(3)
        ->and($user->isFollowedByAuthUser)->toBeFalse()
        ->and($user->createdAt)->toBe($createdAt);
});

it('bioはnullを許容する', function () {
    $user = new User(
        id: 'uuid-1',
        name: 'テストユーザー',
        handle: 'test_user',
        email: 'test@example.com',
        bio: null,
        headerImageUrl: null,
        profileImageUrl: null,
        postsCount: 0,
        followersCount: 0,
        followingCount: 0,
        isFollowedByAuthUser: false,
    );

    expect($user->bio)->toBeNull()
        ->and($user->headerImageUrl)->toBeNull()
        ->and($user->profileImageUrl)->toBeNull()
        ->and($user->createdAt)->toBeNull();
});
