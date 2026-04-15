<?php

use App\Infrastructure\Eloquent\Models\Hashtag;
use App\Infrastructure\Eloquent\Models\Post;

it('ハッシュタグページにnextCursorとhasMoreが含まれる', function () {
    $hashtag = Hashtag::create(['name' => 'Laravel']);
    $post = Post::factory()->create(['content' => '#Laravel の投稿']);
    $post->hashtags()->attach($hashtag->id);

    $this->withoutVite()
        ->get(route('hashtags.show', 'Laravel'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('hashtags/show')
            ->has('posts', 1)
            ->has('nextCursor')
            ->has('hasMore')
            ->where('hasMore', false)
        );
});

it('投稿が21件あるときhasMore=trueを返す', function () {
    $hashtag = Hashtag::create(['name' => 'PHP']);
    $posts = Post::factory()->count(21)->create();
    $posts->each(fn ($post) => $post->hashtags()->attach($hashtag->id));

    $this->withoutVite()
        ->get(route('hashtags.show', 'PHP'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('hasMore', true)
            ->has('posts', 20)
            ->where('nextCursor', fn ($cursor) => $cursor !== null)
        );
});

it('カーソルを指定すると古い投稿を取得できる', function () {
    $hashtag = Hashtag::create(['name' => 'Test']);
    $pivot = '2026-01-10 12:00:00';

    // 新しい投稿 20件 (pivot+1s 〜 pivot+20s)
    $newPosts = Post::factory()->count(20)->sequence(fn ($seq) => [
        'created_at' => date('Y-m-d H:i:s', strtotime($pivot) + $seq->index + 1),
    ])->create();
    $newPosts->each(fn ($p) => $p->hashtags()->attach($hashtag->id));

    // 古い投稿 1件 (pivot-1s)
    $oldPost = Post::factory()->create(['created_at' => date('Y-m-d H:i:s', strtotime($pivot) - 1)]);
    $oldPost->hashtags()->attach($hashtag->id);

    // カーソル = 20件中の最古 (pivot+1) の ISO 8601 文字列
    $cursor = (new DateTimeImmutable(date('Y-m-d H:i:s', strtotime($pivot) + 1)))->format(DateTimeInterface::ATOM);

    $this->withoutVite()
        ->get(route('hashtags.show', ['hashtag' => 'Test', 'cursor' => $cursor]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('posts', 1)
            ->where('hasMore', false)
        );
});
