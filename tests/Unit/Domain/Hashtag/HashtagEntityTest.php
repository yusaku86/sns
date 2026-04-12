<?php

use App\Domain\Hashtag\Entities\Hashtag;

it('ハッシュタグエンティティを生成できる', function () {
    $hashtag = new Hashtag(
        id: 'uuid-1',
        name: 'Laravel',
    );

    expect($hashtag->id)->toBe('uuid-1')
        ->and($hashtag->name)->toBe('Laravel');
});
