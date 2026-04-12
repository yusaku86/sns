<?php

use App\Infrastructure\Eloquent\Models\Like;
use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\Reply;
use App\Infrastructure\Eloquent\Models\User;

it('プロフィールページを表示できる', function () {
    $user = User::factory()->create();

    $this->get(route('users.show', $user))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('users/show')
                ->has('user')
                ->has('posts')
                ->has('replies')
                ->has('likedPosts')
        );
});

it('プロフィールページにユーザーの投稿が含まれる', function () {
    $user = User::factory()->create();
    Post::factory()->count(3)->create(['user_id' => $user->id]);

    $this->get(route('users.show', $user))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('users/show')
                ->has('posts', 3)
        );
});

it('プロフィールページにユーザーのリプライが含まれる', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    Reply::factory()->count(2)->create(['user_id' => $user->id, 'post_id' => $post->id]);

    $this->get(route('users.show', $user))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('users/show')
                ->has('replies', 2)
        );
});

it('プロフィールページにいいねした投稿が含まれる', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    Like::factory()->create(['user_id' => $user->id, 'post_id' => $post->id]);

    $this->get(route('users.show', $user))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('users/show')
                ->has('likedPosts', 1)
        );
});

it('自分のプロフィールを更新できる', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('users.update', $user), [
            'name' => '更新後の名前',
            'bio' => '更新後のbio',
        ])
        ->assertRedirect();

    expect($user->fresh()->name)->toBe('更新後の名前')
        ->and($user->fresh()->bio)->toBe('更新後のbio');
});

it('他のユーザーのプロフィールは更新できない', function () {
    $user = User::factory()->create();
    $other = User::factory()->create(['name' => '変更前']);

    $this->actingAs($user)
        ->put(route('users.update', $other), [
            'name' => '変更後',
            'bio' => null,
        ])
        ->assertForbidden();

    expect($other->fresh()->name)->toBe('変更前');
});
