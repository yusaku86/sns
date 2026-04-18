<?php

namespace App\Infrastructure\Storage;

use App\Application\Post\PostImageStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * 投稿画像をローカルストレージに保存する実装。
 */
class PostImageStorage implements PostImageStorageInterface
{
    /**
     * {@inheritdoc}
     */
    public function storeAll(array $files): array
    {
        return collect($files)
            ->map(fn (UploadedFile $file) => $file->storeAs(
                'post_images',
                Str::uuid().'.'.$file->extension(),
                'local',
            ))
            ->filter()
            ->values()
            ->all();
    }
}
