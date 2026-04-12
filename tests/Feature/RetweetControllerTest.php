<?php

use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\Retweet;
use App\Infrastructure\Eloquent\Models\User;

it('投稿をリツイートできる', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $this->actingAs($user)
        ->post(route('retweets.store', $post))
        ->assertRedirect();

    expect(Retweet::where('user_id', $user->id)->where('post_id', $post->id)->exists())->toBeTrue();
});

it('リツイートを取り消せる', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    Retweet::create(['user_id' => $user->id, 'post_id' => $post->id]);

    $this->actingAs($user)
        ->delete(route('retweets.destroy', $post))
        ->assertRedirect();

    expect(Retweet::where('user_id', $user->id)->where('post_id', $post->id)->exists())->toBeFalse();
});

it('未ログインユーザーはリツイートできない', function () {
    $post = Post::factory()->create();

    $this->post(route('retweets.store', $post))
        ->assertRedirect(route('login'));
});
