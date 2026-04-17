<?php

namespace App\Application\Post;

use Illuminate\Http\UploadedFile;

interface PostImageStorageInterface
{
    /**
     * @param  UploadedFile[]  $files
     * @return string[] ストレージ上のパス（order順）
     */
    public function storeAll(array $files): array;
}
