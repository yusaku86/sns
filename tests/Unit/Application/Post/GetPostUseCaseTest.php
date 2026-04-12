<?php

use App\Application\Post\GetPostUseCase;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Reply\Entities\Reply;
use App\Domain\Reply\Repositories\ReplyRepositoryInterface;

it('投稿と返信一覧を取得できる', function () {
    $now = new DateTimeImmutable;

    $post = new Post(
        id: 'uuid-post-1',
        userId: 'uuid-user-1',
        userName: '投稿者',
        userHandle: 'poster',
        content: '元の投稿',
        createdAt: $now,
        likesCount: 0,
        likedByAuthUser: false,
    );

    $reply = new Reply(
        id: 'uuid-reply-1',
        postId: 'uuid-post-1',
        userId: 'uuid-user-2',
        userName: '返信者',
        userHandle: 'replier',
        content: '返信内容',
        createdAt: $now,
    );

    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('findById')->with('uuid-post-1', null)->andReturn($post);

    $replyRepository = mock(ReplyRepositoryInterface::class);
    $replyRepository->shouldReceive('getByPostId')->with('uuid-post-1')->andReturn([$reply]);

    $useCase = new GetPostUseCase($postRepository, $replyRepository);
    $result = $useCase->execute('uuid-post-1');

    expect($result['post'])->toBe($post)
        ->and($result['replies'])->toHaveCount(1)
        ->and($result['replies'][0])->toBe($reply);
});

it('存在しない投稿IDを指定すると例外を投げる', function () {
    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('findById')->with('uuid-not-found', null)->andReturn(null);

    $replyRepository = mock(ReplyRepositoryInterface::class);

    $useCase = new GetPostUseCase($postRepository, $replyRepository);

    expect(fn () => $useCase->execute('uuid-not-found'))
        ->toThrow(DomainException::class, 'Post not found.');
});
