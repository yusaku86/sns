<?php

use App\Domain\Retweet\Entities\Retweet;

it('リツイートエンティティを生成できる', function () {
    $now = new DateTimeImmutable;

    $retweet = new Retweet(
        id: 'uuid-1',
        userId: 'uuid-user-1',
        postId: 'uuid-post-1',
        createdAt: $now,
    );

    expect($retweet->id)->toBe('uuid-1')
        ->and($retweet->userId)->toBe('uuid-user-1')
        ->and($retweet->postId)->toBe('uuid-post-1')
        ->and($retweet->createdAt)->toBe($now);
});
