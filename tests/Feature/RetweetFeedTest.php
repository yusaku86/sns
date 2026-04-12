<?php

use App\Infrastructure\Eloquent\Models\Follow;
use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\Retweet;
use App\Infrastructure\Eloquent\Models\User;

it('探索ページにリツイートされた投稿が表示される', function () {
    $author = User::factory()->create();
    $retweeter = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $author->id]);
    Retweet::create(['user_id' => $retweeter->id, 'post_id' => $post->id]);

    $this->withoutVite()
        ->get(route('explore'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('explore')
            ->where('posts', function ($posts) use ($retweeter, $post) {
                $retweetEntry = collect($posts)->first(
                    fn ($p) => $p['id'] === $post->id && $p['retweetedByUserName'] === $retweeter->name
                );

                return $retweetEntry !== null;
            })
        );
});

it('タイムラインにフォロー中ユーザーのリツイートが表示される', function () {
    $authUser = User::factory()->create();
    $followed = User::factory()->create();
    $author = User::factory()->create();

    Follow::create(['follower_id' => $authUser->id, 'following_id' => $followed->id]);

    $post = Post::factory()->create(['user_id' => $author->id]);
    Retweet::create(['user_id' => $followed->id, 'post_id' => $post->id]);

    $this->withoutVite()
        ->actingAs($authUser)
        ->get(route('timeline'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('timeline')
            ->where('posts', function ($posts) use ($followed, $post) {
                $retweetEntry = collect($posts)->first(
                    fn ($p) => $p['id'] === $post->id && $p['retweetedByUserName'] === $followed->name
                );

                return $retweetEntry !== null;
            })
        );
});

it('リツイートエントリにretweetIdが含まれる', function () {
    $author = User::factory()->create();
    $retweeter = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $author->id]);
    $retweet = Retweet::create(['user_id' => $retweeter->id, 'post_id' => $post->id]);

    $this->withoutVite()
        ->get(route('explore'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('explore')
            ->where('posts', function ($posts) use ($retweet) {
                $retweetEntry = collect($posts)->first(
                    fn ($p) => $p['retweetId'] === $retweet->id
                );

                return $retweetEntry !== null;
            })
        );
});
