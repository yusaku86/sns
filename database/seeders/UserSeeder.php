<?php

namespace Database\Seeders;

use App\Infrastructure\Eloquent\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 固定ユーザー（開発用ログイン確認に使用）
        User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'bio' => 'テスト用のアカウントです。',
        ]);

        // ランダムユーザーを99人追加（合計100人）
        User::factory(49)->create();
    }
}
