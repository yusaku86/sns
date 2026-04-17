<?php

use App\Application\Post\CreatePostUseCase;
use App\Domain\Hashtag\Repositories\HashtagRepositoryInterface;
use App\Domain\Post\Repositories\PostImageRepositoryInterface;
use App\Domain\Post\Repositories\PostRepositoryInterface;

it('投稿を作成できる', function () {
    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('save')->once();

    $hashtagRepository = mock(HashtagRepositoryInterface::class);
    $hashtagRepository->shouldNotReceive('syncToPost');

    $imageRepository = mock(PostImageRepositoryInterface::class);
    $imageRepository->shouldNotReceive('saveForPost');

    $useCase = new CreatePostUseCase($postRepository, $hashtagRepository, $imageRepository);
    $post = $useCase->execute('uuid-user-1', 'テストユーザー', 'test_user', 'テスト投稿内容');

    expect($post->userId)->toBe('uuid-user-1')
        ->and($post->userName)->toBe('テストユーザー')
        ->and($post->userHandle)->toBe('test_user')
        ->and($post->content)->toBe('テスト投稿内容')
        ->and($post->likesCount)->toBe(0)
        ->and($post->likedByAuthUser)->toBeFalse()
        ->and($post->id)->not->toBeEmpty();
});

it('ハッシュタグ付き投稿を作成するとハッシュタグが保存される', function () {
    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('save')->once();

    $hashtagRepository = mock(HashtagRepositoryInterface::class);
    $hashtagRepository->shouldReceive('syncToPost')
        ->once()
        ->withArgs(fn (array $names, string $postId) => $names === ['Laravel', 'PHP'] && $postId !== '');

    $imageRepository = mock(PostImageRepositoryInterface::class);
    $imageRepository->shouldNotReceive('saveForPost');

    $useCase = new CreatePostUseCase($postRepository, $hashtagRepository, $imageRepository);
    $post = $useCase->execute('uuid-user-1', 'テストユーザー', 'test_user', '#Laravel と #PHP の話');

    expect($post->content)->toBe('#Laravel と #PHP の話');
});

it('同じハッシュタグが複数あっても重複せずに保存される', function () {
    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('save')->once();

    $hashtagRepository = mock(HashtagRepositoryInterface::class);
    $hashtagRepository->shouldReceive('syncToPost')
        ->once()
        ->withArgs(fn (array $names, string $postId) => $names === ['Laravel']);

    $imageRepository = mock(PostImageRepositoryInterface::class);
    $imageRepository->shouldNotReceive('saveForPost');

    $useCase = new CreatePostUseCase($postRepository, $hashtagRepository, $imageRepository);
    $useCase->execute('uuid-user-1', 'テストユーザー', 'test_user', '#Laravel #Laravel #Laravel');
});

it('ハッシュタグなしの投稿ではsyncToPostは呼ばれない', function () {
    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('save')->once();

    $hashtagRepository = mock(HashtagRepositoryInterface::class);
    $hashtagRepository->shouldNotReceive('syncToPost');

    $imageRepository = mock(PostImageRepositoryInterface::class);
    $imageRepository->shouldNotReceive('saveForPost');

    $useCase = new CreatePostUseCase($postRepository, $hashtagRepository, $imageRepository);
    $useCase->execute('uuid-user-1', 'テストユーザー', 'test_user', 'ハッシュタグなしの投稿');
});
