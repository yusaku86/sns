<?php

use App\Infrastructure\Eloquent\Models\Hashtag;
use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\User;

it('ハッシュタグ別投稿一覧ページを表示できる', function () {
    $hashtag = Hashtag::create(['name' => 'Laravel']);
    $post = Post::factory()->create(['content' => '#Laravel の投稿']);
    $post->hashtags()->attach($hashtag->id);

    $this->withoutVite()
        ->get(route('hashtags.show', 'Laravel'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('hashtags/show')
            ->where('hashtag', 'Laravel')
            ->has('posts', 1)
        );
});

it('認証済みユーザーもハッシュタグ別投稿一覧ページを表示できる', function () {
    $user = User::factory()->create();

    $this->withoutVite()
        ->actingAs($user)
        ->get(route('hashtags.show', 'PHP'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('hashtags/show')
            ->where('hashtag', 'PHP')
            ->has('posts', 0)
        );
});

it('該当するハッシュタグがない場合は空の投稿一覧を返す', function () {
    $this->withoutVite()
        ->get(route('hashtags.show', '存在しないタグ'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('hashtags/show')
            ->has('posts', 0)
        );
});

it('投稿作成時にハッシュタグが自動保存される', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('posts.store'), ['content' => '#Laravel と #PHP の投稿です']);

    $post = Post::where('user_id', $user->id)->first();
    expect($post->hashtags->pluck('name')->sort()->values()->all())
        ->toBe(['Laravel', 'PHP']);
});
