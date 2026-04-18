<?php

namespace App\Domain\Like\Repositories;

use App\Domain\Post\Entities\Post;

/**
 * いいねの永続化・取得を担うリポジトリインターフェース。
 */
interface LikeRepositoryInterface
{
    /**
     * いいねが存在するか確認する。
     *
     * @param  string  $userId  ユーザーID
     * @param  string  $postId  投稿ID
     * @return bool いいね済みの場合true
     */
    public function exists(string $userId, string $postId): bool;

    /**
     * いいねを保存する。
     *
     * @param  string  $userId  ユーザーID
     * @param  string  $postId  投稿ID
     */
    public function save(string $userId, string $postId): void;

    /**
     * いいねを削除する。
     *
     * @param  string  $userId  ユーザーID
     * @param  string  $postId  投稿ID
     */
    public function delete(string $userId, string $postId): void;

    /**
     * ユーザーがいいねした投稿一覧を返す。
     *
     * @param  string  $userId  対象ユーザーID
     * @param  string|null  $authUserId  認証ユーザーID（いいね・リツイート状態の付与に使用）
     * @param  int  $limit  取得件数
     * @return Post[]
     */
    public function getLikedPostsByUserId(string $userId, ?string $authUserId = null, int $limit = 20): array;
}
