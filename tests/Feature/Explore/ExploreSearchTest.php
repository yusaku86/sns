<?php

use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\Retweet;
use App\Infrastructure\Eloquent\Models\User;

it('qパラメータなしでは全体一覧を返し query が空文字になる', function () {
    Post::factory()->count(3)->create();

    $this->withoutVite()
        ->get(route('explore'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('explore')
            ->where('query', '')
            ->has('posts', 3)
        );
});

it('qパラメータで本文に一致する投稿のみ返す', function () {
    Post::factory()->create(['content' => 'Laravelは素晴らしい']);
    Post::factory()->create(['content' => 'Reactも好き']);
    Post::factory()->create(['content' => 'Laravelを学んでいます']);

    $this->withoutVite()
        ->get(route('explore', ['q' => 'Laravel']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('explore')
            ->where('query', 'Laravel')
            ->has('posts', 2)
            ->where('hasMore', false)
        );
});

it('qが空文字のときは全体一覧を返す', function () {
    Post::factory()->count(2)->create();

    $this->withoutVite()
        ->get(route('explore', ['q' => '']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('query', '')
            ->has('posts', 2)
        );
});

it('検索結果はリツイートを含まない', function () {
    $author = User::factory()->create();
    $retweeter = User::factory()->create();
    $post = Post::factory()->create(['content' => 'テスト投稿', 'user_id' => $author->id]);
    Retweet::create(['user_id' => $retweeter->id, 'post_id' => $post->id]);

    $this->withoutVite()
        ->get(route('explore', ['q' => 'テスト']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('posts', 1)
        );
});

it('検索結果が21件あるときhasMore=trueを返す', function () {
    Post::factory()->count(21)->create(['content' => '検索テスト']);

    $this->withoutVite()
        ->get(route('explore', ['q' => '検索テスト']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('hasMore', true)
            ->has('posts', 20)
        );
});

it('認証済みユーザーで検索できる', function () {
    $user = User::factory()->create();
    Post::factory()->create(['content' => 'ログイン済み検索']);

    $this->withoutVite()
        ->actingAs($user)
        ->get(route('explore', ['q' => 'ログイン済み']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('posts', 1)
        );
});
