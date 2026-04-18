<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Post\Repositories\PostImageRepositoryInterface;
use App\Infrastructure\Eloquent\Models\PostImage as PostImageModel;

/**
 * Eloquentを使った投稿画像リポジトリの実装。
 */
class EloquentPostImageRepository implements PostImageRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function saveForPost(string $postId, array $paths): void
    {
        foreach ($paths as $order => $path) {
            PostImageModel::create([
                'post_id' => $postId,
                'path' => $path,
                'order' => $order,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByPostId(string $postId): void
    {
        PostImageModel::where('post_id', $postId)->delete();
    }
}
