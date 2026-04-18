<?php

namespace App\Domain\Post\Repositories;

/**
 * 投稿画像の永続化・削除を担うリポジトリインターフェース。
 */
interface PostImageRepositoryInterface
{
    /**
     * 投稿に紐づく画像パスを保存する。
     *
     * @param  string  $postId  投稿ID
     * @param  string[]  $paths  ストレージ上のパス（order順）
     */
    public function saveForPost(string $postId, array $paths): void;

    /**
     * 投稿に紐づく全画像レコードを削除する。
     *
     * @param  string  $postId  投稿ID
     */
    public function deleteByPostId(string $postId): void;
}
