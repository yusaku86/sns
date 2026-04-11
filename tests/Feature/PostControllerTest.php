<?php

use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\User;

it('ログイン済みユーザーは投稿を作成できる', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('posts.store'), ['content' => 'テスト投稿'])
        ->assertRedirect();

    expect(Post::where('user_id', $user->id)->count())->toBe(1);
});

it('未ログインユーザーは投稿できない', function () {
    $this->post(route('posts.store'), ['content' => 'テスト投稿'])
        ->assertRedirect(route('login'));
});

it('140文字を超える投稿は作成できない', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('posts.store'), ['content' => str_repeat('あ', 141)])
        ->assertSessionHasErrors('content');
});

it('自分の投稿を削除できる', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->delete(route('posts.destroy', $post))
        ->assertRedirect();

    expect(Post::find($post->id))->toBeNull();
});

it('他のユーザーの投稿は削除できない', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user)
        ->delete(route('posts.destroy', $post))
        ->assertForbidden();

    expect(Post::find($post->id))->not->toBeNull();
});
