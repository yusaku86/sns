<?php

namespace Database\Seeders;

use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        // 各ユーザーが3〜7件の投稿を作成
        $users->each(function (User $user) {
            Post::factory(rand(3, 7))->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
