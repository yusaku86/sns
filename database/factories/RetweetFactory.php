<?php

namespace Database\Factories;

use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\Retweet;
use App\Infrastructure\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Retweet>
 */
class RetweetFactory extends Factory
{
    protected $model = Retweet::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'created_at' => fake()->dateTimeBetween('-10 years', 'now'),
        ];
    }
}
