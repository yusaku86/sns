<?php

use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\Reply;
use App\Infrastructure\Eloquent\Models\User;

it('ログイン済みユーザーは返信を作成できる', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $this->actingAs($user)
        ->post(route('replies.store', $post), ['content' => 'テスト返信'])
        ->assertRedirect();

    expect(Reply::where('post_id', $post->id)->where('user_id', $user->id)->count())->toBe(1);
});

it('未ログインユーザーは返信できない', function () {
    $post = Post::factory()->create();

    $this->post(route('replies.store', $post), ['content' => 'テスト返信'])
        ->assertRedirect(route('login'));
});

it('140文字を超える返信は作成できない', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $this->actingAs($user)
        ->post(route('replies.store', $post), ['content' => str_repeat('あ', 141)])
        ->assertSessionHasErrors('content');
});

it('空の返信は作成��きない', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $this->actingAs($user)
        ->post(route('replies.store', $post), ['content' => ''])
        ->assertSessionHasErrors('content');
});
