<?php

use App\Application\User\GetUserProfileUseCase;
use App\Domain\Follow\Repositories\FollowRepositoryInterface;
use App\Domain\Like\Repositories\LikeRepositoryInterface;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Reply\Repositories\ReplyRepositoryInterface;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;

function makeUserEntity(): User
{
    return new User(
        id: 'user-1',
        name: 'テストユーザー',
        handle: 'test_user',
        email: 'test@example.com',
        bio: null,
        headerImageUrl: null,
        profileImageUrl: null,
        postsCount: 0,
        followersCount: 0,
        followingCount: 0,
        isFollowedByAuthUser: false,
    );
}

function makeUserPost(string $id, string $createdAt): Post
{
    return new Post(
        id: $id,
        userId: 'user-1',
        userName: 'テストユーザー',
        userHandle: 'test_user',
        content: '投稿'.$id,
        createdAt: new DateTimeImmutable($createdAt),
        likesCount: 0,
        likedByAuthUser: false,
    );
}

function makeSubMocks(): array
{
    $replyRepo = mock(ReplyRepositoryInterface::class);
    $likeRepo = mock(LikeRepositoryInterface::class);
    $followRepo = mock(FollowRepositoryInterface::class);

    $replyRepo->shouldReceive('getByUserId')->andReturn([]);
    $likeRepo->shouldReceive('getLikedPostsByUserId')->andReturn([]);
    $followRepo->shouldReceive('getFollowers')->andReturn([]);
    $followRepo->shouldReceive('getFollowing')->andReturn([]);

    return [$replyRepo, $likeRepo, $followRepo];
}

it('カーソルなし・20件以下の場合はhasMore=falseを返す', function () {
    [$replyRepo, $likeRepo, $followRepo] = makeSubMocks();

    $userRepo = mock(UserRepositoryInterface::class);
    $userRepo->shouldReceive('findById')->andReturn(makeUserEntity());

    $postRepo = mock(PostRepositoryInterface::class);
    $posts = array_map(fn ($i) => makeUserPost("p{$i}", '2026-01-01 00:00:00'), range(1, 5));
    $postRepo->shouldReceive('getByUserId')
        ->once()
        ->with('user-1', null, 21, null)
        ->andReturn($posts);

    $useCase = new GetUserProfileUseCase($userRepo, $postRepo, $replyRepo, $likeRepo, $followRepo);
    $result = $useCase->execute('user-1');

    expect($result['posts'])->toHaveCount(5)
        ->and($result['hasMore'])->toBeFalse()
        ->and($result['nextCursor'])->toBeNull();
});

it('21件返ってきた場合はhasMore=trueで20件にトリムする', function () {
    [$replyRepo, $likeRepo, $followRepo] = makeSubMocks();

    $userRepo = mock(UserRepositoryInterface::class);
    $userRepo->shouldReceive('findById')->andReturn(makeUserEntity());

    $postRepo = mock(PostRepositoryInterface::class);
    $posts = array_map(fn ($i) => makeUserPost("p{$i}", "2026-04-{$i}T12:00:00+00:00"), range(1, 21));
    $postRepo->shouldReceive('getByUserId')
        ->once()
        ->with('user-1', null, 21, null)
        ->andReturn($posts);

    $useCase = new GetUserProfileUseCase($userRepo, $postRepo, $replyRepo, $likeRepo, $followRepo);
    $result = $useCase->execute('user-1');

    expect($result['posts'])->toHaveCount(20)
        ->and($result['hasMore'])->toBeTrue()
        ->and($result['nextCursor'])->not->toBeNull();
});

it('カーソルありの場合はリポジトリにカーソルを渡す', function () {
    [$replyRepo, $likeRepo, $followRepo] = makeSubMocks();

    $userRepo = mock(UserRepositoryInterface::class);
    $userRepo->shouldReceive('findById')->andReturn(makeUserEntity());

    $postRepo = mock(PostRepositoryInterface::class);
    $cursor = '2026-04-14T12:00:00+00:00';
    $posts = [makeUserPost('p1', '2026-04-13T00:00:00+00:00')];
    $postRepo->shouldReceive('getByUserId')
        ->once()
        ->with('user-1', null, 21, $cursor)
        ->andReturn($posts);

    $useCase = new GetUserProfileUseCase($userRepo, $postRepo, $replyRepo, $likeRepo, $followRepo);
    $result = $useCase->execute('user-1', null, $cursor);

    expect($result['posts'])->toHaveCount(1)
        ->and($result['hasMore'])->toBeFalse();
});

it('ユーザーが存在しない場合はnullを返す', function () {
    [$replyRepo, $likeRepo, $followRepo] = makeSubMocks();

    $userRepo = mock(UserRepositoryInterface::class);
    $userRepo->shouldReceive('findById')->with('non-existent', null)->andReturn(null);

    $postRepo = mock(PostRepositoryInterface::class);

    $useCase = new GetUserProfileUseCase($userRepo, $postRepo, $replyRepo, $likeRepo, $followRepo);
    $result = $useCase->execute('non-existent');

    expect($result)->toBeNull();
});
