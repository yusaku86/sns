<?php

namespace App\Infrastructure\Storage;

use App\Application\Post\PostImageStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PostImageStorage implements PostImageStorageInterface
{
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
