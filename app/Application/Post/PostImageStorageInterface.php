<?php

namespace App\Application\Post;

use Illuminate\Http\UploadedFile;

/**
 * 投稿画像のストレージ保存を抽象化するインターフェース。
 */
interface PostImageStorageInterface
{
    /**
     * 複数の画像ファイルをストレージに保存してパスを返す。
     *
     * @param  UploadedFile[]  $files  アップロードされたファイルの配列
     * @return string[] ストレージ上のパス（order順）
     */
    public function storeAll(array $files): array;
}
