<?php

use App\Infrastructure\Eloquent\Models\Follow;
use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\User;

it('タイムラインにnextCursorとhasMoreが含まれる', function () {
    $user = User::factory()->create();
    $following = User::factory()->create();
    Follow::create(['follower_id' => $user->id, 'following_id' => $following->id]);
    Post::factory()->count(3)->create(['user_id' => $following->id]);

    $this->withoutVite()
        ->actingAs($user)
        ->get(route('timeline'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('timeline')
            ->has('posts', 3)
            ->has('nextCursor')
            ->has('hasMore')
            ->where('hasMore', false)
        );
});

it('投稿が21件あるときhasMore=trueとnextCursorを返す', function () {
    $user = User::factory()->create();
    $following = User::factory()->create();
    Follow::create(['follower_id' => $user->id, 'following_id' => $following->id]);
    Post::factory()->count(21)->create(['user_id' => $following->id]);

    $this->withoutVite()
        ->actingAs($user)
        ->get(route('timeline'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('hasMore', true)
            ->has('posts', 20)
            ->where('nextCursor', fn ($cursor) => $cursor !== null)
        );
});

it('カーソルを指定すると古い投稿を取得できる', function () {
    $user = User::factory()->create();
    $following = User::factory()->create();
    Follow::create(['follower_id' => $user->id, 'following_id' => $following->id]);

    $pivot = '2026-01-10 12:00:00';

    // 新しい投稿 20件 (pivot+1s 〜 pivot+20s)
    Post::factory()->count(20)->sequence(fn ($seq) => [
        'user_id' => $following->id,
        'created_at' => date('Y-m-d H:i:s', strtotime($pivot) + $seq->index + 1),
    ])->create();

    // 古い投稿 1件 (pivot-1s)
    Post::factory()->create([
        'user_id' => $following->id,
        'created_at' => date('Y-m-d H:i:s', strtotime($pivot) - 1),
    ]);

    // カーソル = 20件中の最古 (pivot+1) の ISO 8601 文字列
    $cursor = (new DateTimeImmutable(date('Y-m-d H:i:s', strtotime($pivot) + 1)))->format(DateTimeInterface::ATOM);

    $this->withoutVite()
        ->actingAs($user)
        ->get(route('timeline', ['cursor' => $cursor]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('posts', 1)
            ->where('hasMore', false)
        );
});
