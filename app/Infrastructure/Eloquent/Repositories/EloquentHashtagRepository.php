<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Hashtag\Entities\Hashtag as HashtagEntity;
use App\Domain\Hashtag\Repositories\HashtagRepositoryInterface;
use App\Infrastructure\Eloquent\Models\Hashtag as HashtagModel;
use App\Infrastructure\Eloquent\Models\Post;

/**
 * Eloquentを使ったハッシュタグリポジトリの実装。
 */
class EloquentHashtagRepository implements HashtagRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findOrCreateByName(string $name): HashtagEntity
    {
        $model = HashtagModel::firstOrCreate(['name' => $name]);

        return new HashtagEntity(
            id: $model->id,
            name: $model->name,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function syncToPost(array $names, string $postId): void
    {
        $ids = collect($names)->map(function (string $name) {
            return HashtagModel::firstOrCreate(['name' => $name])->id;
        })->all();

        $post = Post::findOrFail($postId);
        $post->hashtags()->sync($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrending(int $limit = 5): array
    {
        return HashtagModel::withCount('posts')
            ->orderByDesc('posts_count')
            ->limit($limit)
            ->get()
            ->map(fn ($model) => new HashtagEntity(
                id: $model->id,
                name: $model->name,
                postsCount: $model->posts_count,
            ))
            ->all();
    }
}
