<?php

namespace Database\Factories;

use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\Reply;
use App\Infrastructure\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reply>
 */
class ReplyFactory extends Factory
{
    protected $model = Reply::class;

    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'content' => fake()->realText(100),
        ];
    }
}
