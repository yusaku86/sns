<?php

namespace App\Domain\Post\Repositories;

interface PostImageRepositoryInterface
{
    /**
     * 投稿に紐づく画像パスを保存する
     *
     * @param  string[]  $paths  ストレージ上のパス（order順）
     */
    public function saveForPost(string $postId, array $paths): void;

    /** 投稿に紐づく全画像レコードを削除する */
    public function deleteByPostId(string $postId): void;
}
