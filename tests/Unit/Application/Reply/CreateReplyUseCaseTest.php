<?php

use App\Application\Reply\CreateReplyUseCase;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Reply\Repositories\ReplyRepositoryInterface;

it('返信を作成できる', function () {
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

    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('findById')->with('uuid-post-1')->andReturn($post);

    $replyRepository = mock(ReplyRepositoryInterface::class);
    $replyRepository->shouldReceive('save')->once();

    $useCase = new CreateReplyUseCase($postRepository, $replyRepository);
    $reply = $useCase->execute('uuid-post-1', 'uuid-user-2', '返信者', 'replier', '返信内容');

    expect($reply->postId)->toBe('uuid-post-1')
        ->and($reply->userId)->toBe('uuid-user-2')
        ->and($reply->userName)->toBe('返信者')
        ->and($reply->userHandle)->toBe('replier')
        ->and($reply->content)->toBe('返信内容')
        ->and($reply->id)->not->toBeEmpty();
});

it('存在しない投稿への返信は例外を投げる', function () {
    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('findById')->with('uuid-not-found')->andReturn(null);

    $replyRepository = mock(ReplyRepositoryInterface::class);

    $useCase = new CreateReplyUseCase($postRepository, $replyRepository);

    expect(fn () => $useCase->execute('uuid-not-found', 'uuid-user-1', 'ユーザー', 'user', '返信内容'))
        ->toThrow(DomainException::class, 'Post not found.');
});
