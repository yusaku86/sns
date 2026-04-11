<?php

use App\Infrastructure\Eloquent\Models\Like;
use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\User;

it('投稿にいいねできる', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $this->actingAs($user)
        ->post(route('likes.store', $post))
        ->assertRedirect();

    expect(Like::where('user_id', $user->id)->where('post_id', $post->id)->exists())->toBeTrue();
});

it('いいねを取り消せる', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    Like::create(['user_id' => $user->id, 'post_id' => $post->id]);

    $this->actingAs($user)
        ->delete(route('likes.destroy', $post))
        ->assertRedirect();

    expect(Like::where('user_id', $user->id)->where('post_id', $post->id)->exists())->toBeFalse();
});

it('未ログインユーザーはいいねできない', function () {
    $post = Post::factory()->create();

    $this->post(route('likes.store', $post))
        ->assertRedirect(route('login'));
});
