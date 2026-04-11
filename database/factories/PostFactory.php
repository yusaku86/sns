<?php

namespace Database\Factories;

use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'content' => fake()->realText(100),
        ];
    }
}
