<?php

use App\Domain\Like\Entities\Like;

it('いいねエンティティを生成できる', function () {
    $now = new DateTimeImmutable;

    $like = new Like(
        id: 'uuid-1',
        userId: 'uuid-user-1',
        postId: 'uuid-post-1',
        createdAt: $now,
    );

    expect($like->id)->toBe('uuid-1')
        ->and($like->userId)->toBe('uuid-user-1')
        ->and($like->postId)->toBe('uuid-post-1')
        ->and($like->createdAt)->toBe($now);
});
