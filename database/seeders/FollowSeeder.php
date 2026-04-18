<?php

namespace Database\Seeders;

use App\Infrastructure\Eloquent\Models\Follow;
use App\Infrastructure\Eloquent\Models\User;
use Illuminate\Database\Seeder;

class FollowSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $users->each(function (User $follower) use ($users) {
            // 自分以外のユーザーからランダムに1 ~ 40人をフォロー
            $targets = $users
                ->where('id', '!=', $follower->id)
                ->random(min(rand(1, 40), $users->count() - 1));

            $targets->each(function (User $following) use ($follower) {
                // 重複を避けて作成
                Follow::firstOrCreate([
                    'follower_id' => $follower->id,
                    'following_id' => $following->id,
                ]);
            });
        });
    }
}
