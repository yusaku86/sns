<?php

use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\Reply;
use App\Infrastructure\Eloquent\Models\User;

it('投稿詳細ページを表示できる', function () {
    $post = Post::factory()->create();

    $this->withoutVite()
        ->get(route('posts.show', $post))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('posts/show')
            ->has('post')
            ->has('replies')
        );
});

it('認証済みユーザーは自分のいいね状態が含まれた投稿詳細を取得できる', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $this->withoutVite()
        ->actingAs($user)
        ->get(route('posts.show', $post))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('posts/show'));
});

it('投稿詳細ページで返信一覧が表示される', function () {
    $post = Post::factory()->create();
    Reply::factory()->count(3)->create(['post_id' => $post->id]);

    $this->withoutVite()
        ->get(route('posts.show', $post))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('posts/show')
            ->has('replies', 3)
        );
});

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
