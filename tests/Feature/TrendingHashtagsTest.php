<?php

use App\Domain\Hashtag\Entities\Hashtag as HashtagEntity;
use App\Infrastructure\Eloquent\Models\Hashtag;
use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\User;
use App\Jobs\UpdateTrendingHashtagsJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

beforeEach(fn () => Cache::flush());

it('ページの shared props に trendingHashtags が含まれる', function () {
    $hashtag = Hashtag::create(['name' => 'Laravel']);
    $post = Post::factory()->create(['content' => '#Laravel の投稿']);
    $post->hashtags()->attach($hashtag->id);

    $this->withoutVite()
        ->get(route('hashtags.show', 'Laravel'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('trendingHashtags')
        );
});

it('trendingHashtags は投稿数の多い順に最大5件返す', function () {
    foreach (['C' => 1, 'B' => 2, 'A' => 3] as $name => $count) {
        $hashtag = Hashtag::create(['name' => $name]);
        $posts = Post::factory()->count($count)->create();
        foreach ($posts as $post) {
            $post->hashtags()->attach($hashtag->id);
        }
    }

    // Observer による途中キャッシュを破棄し、全データが揃った状態で DB から取得させる
    Cache::flush();

    $this->withoutVite()
        ->get(route('hashtags.show', 'A'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('trendingHashtags.0.name', 'A')
            ->where('trendingHashtags.0.postsCount', 3)
            ->where('trendingHashtags.1.name', 'B')
            ->where('trendingHashtags.1.postsCount', 2)
            ->where('trendingHashtags.2.name', 'C')
            ->where('trendingHashtags.2.postsCount', 1)
        );
});

it('PostObserver: 投稿作成時に UpdateTrendingHashtagsJob がキューに積まれる', function () {
    Queue::fake();

    Post::factory()->create();

    Queue::assertPushed(UpdateTrendingHashtagsJob::class);
});

it('PostObserver: 投稿削除時に UpdateTrendingHashtagsJob がキューに積まれる', function () {
    // Post を先に作成してから Queue::fake() を有効化し、削除イベントのみを検証する
    $post = Post::factory()->create();

    Queue::fake();
    $post->delete();

    Queue::assertPushed(UpdateTrendingHashtagsJob::class);
});

it('投稿作成（HTTP経由）でも Observer を通じてジョブがキューに積まれる', function () {
    Queue::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('posts.store'), ['content' => '#Laravel の投稿']);

    Queue::assertPushed(UpdateTrendingHashtagsJob::class);
});

it('投稿削除（HTTP経由）でも Observer を通じてジョブがキューに積まれる', function () {
    Queue::fake();

    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->delete(route('posts.destroy', $post));

    Queue::assertPushed(UpdateTrendingHashtagsJob::class);
});

it('キャッシュがある場合は DB にアクセスせず shared props を返す', function () {
    Cache::put('trending_hashtags', [
        new HashtagEntity(id: 'uuid-cached', name: 'Cached', postsCount: 999),
    ], 300);

    $this->withoutVite()
        ->get(route('hashtags.show', 'X'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('trendingHashtags.0.name', 'Cached')
            ->where('trendingHashtags.0.postsCount', 999)
        );
});
