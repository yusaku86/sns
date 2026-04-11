<?php

namespace Database\Seeders;

use App\Infrastructure\Eloquent\Models\Like;
use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\User;
use Illuminate\Database\Seeder;

class LikeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $posts = Post::all();

        $users->each(function (User $user) use ($posts) {
            // 各ユーザーがランダムな投稿に1〜10件いいね
            $targets = $posts
                ->where('user_id', '!=', $user->id) // 自分の投稿以外
                ->random(min(rand(1, 10), $posts->count()));

            $targets->each(function (Post $post) use ($user) {
                // 重複を避けて作成
                Like::firstOrCreate([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                ]);
            });
        });
    }
}
