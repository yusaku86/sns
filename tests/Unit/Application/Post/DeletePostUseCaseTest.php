<?php

use App\Application\Post\DeletePostUseCase;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;

it('自分の投稿を削除できる', function () {
    $post = new Post(
        id: 'uuid-post-1',
        userId: 'uuid-user-1',
        userName: 'テストユーザー',
        content: '削除対象の投稿',
        createdAt: new DateTimeImmutable,
        likesCount: 0,
        likedByAuthUser: false,
    );

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('findById')->with('uuid-post-1')->andReturn($post);
    $repository->shouldReceive('delete')->with('uuid-post-1')->once();

    $useCase = new DeletePostUseCase($repository);
    $useCase->execute('uuid-post-1', 'uuid-user-1');
});

it('他のユーザーの投稿は削除できない', function () {
    $post = new Post(
        id: 'uuid-post-1',
        userId: 'uuid-user-1',
        userName: 'テストユーザー',
        content: '削除対象の投稿',
        createdAt: new DateTimeImmutable,
        likesCount: 0,
        likedByAuthUser: false,
    );

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('findById')->with('uuid-post-1')->andReturn($post);
    $repository->shouldNotReceive('delete');

    $useCase = new DeletePostUseCase($repository);

    expect(fn () => $useCase->execute('uuid-post-1', 'uuid-user-2'))
        ->toThrow(AuthorizationException::class);
});

it('存在しない投稿の削除は何もしない', function () {
    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('findById')->with('uuid-post-999')->andReturn(null);
    $repository->shouldNotReceive('delete');

    $useCase = new DeletePostUseCase($repository);
    $useCase->execute('uuid-post-999', 'uuid-user-1');
});
