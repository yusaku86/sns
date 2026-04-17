<?php

use App\Application\Post\CreatePostUseCase;
use App\Domain\Hashtag\Repositories\HashtagRepositoryInterface;
use App\Domain\Post\Repositories\PostImageRepositoryInterface;
use App\Domain\Post\Repositories\PostRepositoryInterface;

it('画像パスなしで投稿を作成した場合、saveForPostは呼ばれない', function () {
    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('save')->once();

    $hashtagRepository = mock(HashtagRepositoryInterface::class);
    $hashtagRepository->shouldNotReceive('syncToPost');

    $imageRepository = mock(PostImageRepositoryInterface::class);
    $imageRepository->shouldNotReceive('saveForPost');

    $useCase = new CreatePostUseCase($postRepository, $hashtagRepository, $imageRepository);
    $post = $useCase->execute('uuid-post-1', 'uuid-user-1', 'テストユーザー', 'test_user', 'テスト投稿内容');

    expect($post->images)->toBe([]);
});

it('画像パスありで投稿を作成した場合、saveForPostが呼ばれる', function () {
    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('save')->once();

    $hashtagRepository = mock(HashtagRepositoryInterface::class);
    $hashtagRepository->shouldNotReceive('syncToPost');

    $imageRepository = mock(PostImageRepositoryInterface::class);
    $imageRepository->shouldReceive('saveForPost')
        ->once()
        ->withArgs(fn (string $postId, array $paths) => $postId === 'uuid-post-1' && $paths === ['post_images/a.jpg', 'post_images/b.jpg']
        );

    $useCase = new CreatePostUseCase($postRepository, $hashtagRepository, $imageRepository);
    $post = $useCase->execute(
        'uuid-post-1', 'uuid-user-1', 'テストユーザー', 'test_user', 'テスト投稿',
        ['post_images/a.jpg', 'post_images/b.jpg'],
    );

    expect($post->id)->toBe('uuid-post-1');
});

it('画像は最大8枚まで保存できる', function () {
    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('save')->once();

    $hashtagRepository = mock(HashtagRepositoryInterface::class);
    $hashtagRepository->shouldNotReceive('syncToPost');

    $paths = array_map(fn ($i) => "post_images/img{$i}.jpg", range(1, 8));

    $imageRepository = mock(PostImageRepositoryInterface::class);
    $imageRepository->shouldReceive('saveForPost')
        ->once()
        ->withArgs(fn (string $postId, array $p) => count($p) === 8);

    $useCase = new CreatePostUseCase($postRepository, $hashtagRepository, $imageRepository);
    $useCase->execute('uuid-post-1', 'uuid-user-1', 'テストユーザー', 'test_user', 'テスト', $paths);
});
