<?php

use App\Domain\Follow\Entities\Follow;

it('フォローエンティティを生成できる', function () {
    $now = new DateTimeImmutable;

    $follow = new Follow(
        id: 'uuid-1',
        followerId: 'uuid-user-1',
        followingId: 'uuid-user-2',
        createdAt: $now,
    );

    expect($follow->id)->toBe('uuid-1')
        ->and($follow->followerId)->toBe('uuid-user-1')
        ->and($follow->followingId)->toBe('uuid-user-2')
        ->and($follow->createdAt)->toBe($now);
});
