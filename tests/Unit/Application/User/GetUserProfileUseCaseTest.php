<?php

use App\Application\Shared\FeedMerger;
use App\Application\User\GetUserProfileUseCase;
use App\Domain\Follow\Repositories\FollowRepositoryInterface;
use App\Domain\Like\Repositories\LikeRepositoryInterface;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Reply\Repositories\ReplyRepositoryInterface;
use App\Domain\Retweet\Repositories\RetweetRepositoryInterface;
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

function makeProfileUseCase(
    array $posts,
    array $retweets = [],
    ?string $cursor = null,
): GetUserProfileUseCase {
    [$replyRepo, $likeRepo, $followRepo] = makeSubMocks();

    $userRepo = mock(UserRepositoryInterface::class);
    $userRepo->shouldReceive('findById')->andReturn(makeUserEntity());

    $postRepo = mock(PostRepositoryInterface::class);
    $postRepo->shouldReceive('getByUserId')
        ->with('user-1', null, 21, $cursor)
        ->andReturn($posts);

    $retweetRepo = mock(RetweetRepositoryInterface::class);
    $retweetRepo->shouldReceive('getByUserIdAsPost')
        ->with('user-1', null, 21, $cursor)
        ->andReturn($retweets);

    return new GetUserProfileUseCase($userRepo, $postRepo, $retweetRepo, $replyRepo, $likeRepo, $followRepo, new FeedMerger);
}

it('カーソルなし・20件以下の場合はhasMore=falseを返す', function () {
    $posts = array_map(fn ($i) => makeUserPost("p{$i}", '2026-01-01 00:00:00'), range(1, 5));
    $result = makeProfileUseCase($posts)->execute('user-1');

    expect($result['posts'])->toHaveCount(5)
        ->and($result['hasMore'])->toBeFalse()
        ->and($result['nextCursor'])->toBeNull();
});

it('投稿とリツイートを合わせて21件以上あるときhasMore=trueで20件にトリムする', function () {
    $posts = array_map(fn ($i) => makeUserPost("p{$i}", "2026-04-{$i}T12:00:00+00:00"), range(1, 20));
    $retweets = [makeUserPost('rt1', '2025-12-01T00:00:00+00:00')];
    $result = makeProfileUseCase($posts, $retweets)->execute('user-1');

    expect($result['posts'])->toHaveCount(20)
        ->and($result['hasMore'])->toBeTrue()
        ->and($result['nextCursor'])->not->toBeNull();
});

it('カーソルありの場合はリポジトリにカーソルを渡す', function () {
    $cursor = '2026-04-14T12:00:00+00:00';
    $posts = [makeUserPost('p1', '2026-04-13T00:00:00+00:00')];
    $result = makeProfileUseCase($posts, [], $cursor)->execute('user-1', null, $cursor);

    expect($result['posts'])->toHaveCount(1)
        ->and($result['hasMore'])->toBeFalse();
});

it('ユーザーが存在しない場合はnullを返す', function () {
    [$replyRepo, $likeRepo, $followRepo] = makeSubMocks();

    $userRepo = mock(UserRepositoryInterface::class);
    $userRepo->shouldReceive('findById')->with('non-existent', null)->andReturn(null);

    $postRepo = mock(PostRepositoryInterface::class);
    $retweetRepo = mock(RetweetRepositoryInterface::class);

    $useCase = new GetUserProfileUseCase($userRepo, $postRepo, $retweetRepo, $replyRepo, $likeRepo, $followRepo, new FeedMerger);
    $result = $useCase->execute('non-existent');

    expect($result)->toBeNull();
});
