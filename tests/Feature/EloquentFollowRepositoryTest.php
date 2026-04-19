<?php

use App\Infrastructure\Eloquent\Models\Follow;
use App\Infrastructure\Eloquent\Models\User;
use App\Infrastructure\Eloquent\Repositories\EloquentFollowRepository;

it('getFollowers: フォロワー一覧を返す', function () {
    $user = User::factory()->create();
    $follower1 = User::factory()->create();
    $follower2 = User::factory()->create();

    Follow::create(['follower_id' => $follower1->id, 'following_id' => $user->id]);
    Follow::create(['follower_id' => $follower2->id, 'following_id' => $user->id]);

    $repository = new EloquentFollowRepository;
    $result = $repository->getFollowers($user->id);

    expect($result)->toHaveCount(2);
    expect(collect($result)->pluck('id')->all())->toContain($follower1->id, $follower2->id);
});

it('getFollowers: フォロワーがいない場合は空配列を返す', function () {
    $user = User::factory()->create();

    $repository = new EloquentFollowRepository;
    $result = $repository->getFollowers($user->id);

    expect($result)->toBeEmpty();
});

it('getFollowers: 認証ユーザーがフォロー済みの場合 isFollowedByAuthUser が true になる', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create();
    $follower = User::factory()->create();

    // follower が user をフォロー
    Follow::create(['follower_id' => $follower->id, 'following_id' => $user->id]);
    // authUser が follower をフォロー
    Follow::create(['follower_id' => $authUser->id, 'following_id' => $follower->id]);

    $repository = new EloquentFollowRepository;
    $result = $repository->getFollowers($user->id, $authUser->id);

    expect($result)->toHaveCount(1);
    expect($result[0]->isFollowedByAuthUser)->toBeTrue();
});

it('getFollowers: 認証ユーザーが未フォローの場合 isFollowedByAuthUser が false になる', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create();
    $follower = User::factory()->create();

    Follow::create(['follower_id' => $follower->id, 'following_id' => $user->id]);

    $repository = new EloquentFollowRepository;
    $result = $repository->getFollowers($user->id, $authUser->id);

    expect($result)->toHaveCount(1);
    expect($result[0]->isFollowedByAuthUser)->toBeFalse();
});

it('getFollowing: フォロー中一覧を返す', function () {
    $user = User::factory()->create();
    $target1 = User::factory()->create();
    $target2 = User::factory()->create();

    Follow::create(['follower_id' => $user->id, 'following_id' => $target1->id]);
    Follow::create(['follower_id' => $user->id, 'following_id' => $target2->id]);

    $repository = new EloquentFollowRepository;
    $result = $repository->getFollowing($user->id);

    expect($result)->toHaveCount(2);
    expect(collect($result)->pluck('id')->all())->toContain($target1->id, $target2->id);
});

it('getFollowing: フォロー中がいない場合は空配列を返す', function () {
    $user = User::factory()->create();

    $repository = new EloquentFollowRepository;
    $result = $repository->getFollowing($user->id);

    expect($result)->toBeEmpty();
});

it('getFollowing: 認証ユーザーがフォロー済みの場合 isFollowedByAuthUser が true になる', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create();
    $target = User::factory()->create();

    // user が target をフォロー
    Follow::create(['follower_id' => $user->id, 'following_id' => $target->id]);
    // authUser が target をフォロー
    Follow::create(['follower_id' => $authUser->id, 'following_id' => $target->id]);

    $repository = new EloquentFollowRepository;
    $result = $repository->getFollowing($user->id, $authUser->id);

    expect($result)->toHaveCount(1);
    expect($result[0]->isFollowedByAuthUser)->toBeTrue();
});

it('getFollowing: 認証ユーザーが未フォローの場合 isFollowedByAuthUser が false になる', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create();
    $target = User::factory()->create();

    Follow::create(['follower_id' => $user->id, 'following_id' => $target->id]);

    $repository = new EloquentFollowRepository;
    $result = $repository->getFollowing($user->id, $authUser->id);

    expect($result)->toHaveCount(1);
    expect($result[0]->isFollowedByAuthUser)->toBeFalse();
});

it('getSuggestedUsers: 自分がフォローしているユーザーのフォロイーをフォロワー数順で返す', function () {
    $auth = User::factory()->create();
    $following = User::factory()->create();
    $suggested = User::factory()->create();
    $other = User::factory()->create();

    Follow::create(['follower_id' => $auth->id, 'following_id' => $following->id]);
    Follow::create(['follower_id' => $following->id, 'following_id' => $suggested->id]);
    Follow::create(['follower_id' => $following->id, 'following_id' => $other->id]);

    $repository = new EloquentFollowRepository;
    $result = $repository->getSuggestedUsers($auth->id, 5);

    expect(collect($result)->pluck('id')->all())->toContain($suggested->id, $other->id);
});

it('getSuggestedUsers: 既にフォロー済みのユーザーは除外する', function () {
    $auth = User::factory()->create();
    $following = User::factory()->create();
    $alreadyFollowed = User::factory()->create();

    Follow::create(['follower_id' => $auth->id, 'following_id' => $following->id]);
    Follow::create(['follower_id' => $auth->id, 'following_id' => $alreadyFollowed->id]);
    Follow::create(['follower_id' => $following->id, 'following_id' => $alreadyFollowed->id]);

    $repository = new EloquentFollowRepository;
    $result = $repository->getSuggestedUsers($auth->id, 5);

    expect(collect($result)->pluck('id')->all())->not->toContain($alreadyFollowed->id);
});

it('getFollowers: 返される FollowUser の各フィールドが正しい', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create(['name' => 'テストユーザー']);

    Follow::create(['follower_id' => $follower->id, 'following_id' => $user->id]);

    $repository = new EloquentFollowRepository;
    $result = $repository->getFollowers($user->id);

    expect($result[0]->id)->toBe($follower->id)
        ->and($result[0]->name)->toBe('テストユーザー')
        ->and($result[0]->handle)->toBe($follower->handle);
});
