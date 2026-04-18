<?php

namespace App\Domain\Retweet\Repositories;

/**
 * リツイートの永続化を担うリポジトリインターフェース。
 */
interface RetweetRepositoryInterface
{
    /**
     * リツイートが存在するか確認する。
     *
     * @param  string  $userId  ユーザーID
     * @param  string  $postId  投稿ID
     * @return bool リツイート済みの場合true
     */
    public function exists(string $userId, string $postId): bool;

    /**
     * リツイートを保存する。
     *
     * @param  string  $userId  ユーザーID
     * @param  string  $postId  投稿ID
     */
    public function save(string $userId, string $postId): void;

    /**
     * リツイートを削除する。
     *
     * @param  string  $userId  ユーザーID
     * @param  string  $postId  投稿ID
     */
    public function delete(string $userId, string $postId): void;
}
