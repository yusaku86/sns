<?php

use App\Domain\Post\Entities\PostImage;

it('画像エンティティを生成できる', function () {
    $image = new PostImage(
        id: 'uuid-img-1',
        postId: 'uuid-post-1',
        path: 'post_images/test.jpg',
        order: 0,
    );

    expect($image->id)->toBe('uuid-img-1')
        ->and($image->postId)->toBe('uuid-post-1')
        ->and($image->path)->toBe('post_images/test.jpg')
        ->and($image->order)->toBe(0);
});

it('複数の画像エンティティをorderで並べられる', function () {
    $images = [
        new PostImage(id: 'uuid-img-2', postId: 'uuid-post-1', path: 'post_images/2.jpg', order: 1),
        new PostImage(id: 'uuid-img-1', postId: 'uuid-post-1', path: 'post_images/1.jpg', order: 0),
    ];

    usort($images, fn (PostImage $a, PostImage $b) => $a->order <=> $b->order);

    expect($images[0]->order)->toBe(0)
        ->and($images[1]->order)->toBe(1);
});
