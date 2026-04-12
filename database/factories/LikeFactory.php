<?php

namespace Database\Factories;

use App\Infrastructure\Eloquent\Models\Like;
use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Like>
 */
class LikeFactory extends Factory
{
    protected $model = Like::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
        ];
    }
}
