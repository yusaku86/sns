<?php

namespace App\Domain\Retweet\Repositories;

use App\Domain\Post\Entities\Post;

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

    /**
     * 指定ユーザーのタイムライン向けリツイートをPost[]として返す。
     * フォロー中ユーザー＋自分のリツイートを日時降順で取得する。
     *
     * @param  string  $userId  タイムラインを表示するユーザーID
     * @param  string|null  $authUserId  認証ユーザーID（いいね・リツイート状態の付与に使用）
     * @param  int  $limit  取得件数
     * @param  string|null  $cursor  ページネーションカーソル
     * @return Post[]
     */
    public function getForTimeline(string $userId, ?string $authUserId, int $limit = 20, ?string $cursor = null): array;

    /**
     * 全リツイートをPost[]として返す（探索ページ用）。
     *
     * @param  string|null  $authUserId  認証ユーザーID
     * @param  int  $limit  取得件数
     * @param  string|null  $cursor  ページネーションカーソル
     * @return Post[]
     */
    public function getAllAsPost(?string $authUserId = null, int $limit = 20, ?string $cursor = null): array;

    /**
     * 指定ユーザーのリツイートをPost[]として返す（プロフィール投稿タブ用）。
     *
     * @param  string  $userId  対象ユーザーID
     * @param  string|null  $authUserId  認証ユーザーID
     * @param  int  $limit  取得件数
     * @param  string|null  $cursor  ページネーションカーソル
     * @return Post[]
     */
    public function getByUserIdAsPost(string $userId, ?string $authUserId = null, int $limit = 20, ?string $cursor = null): array;
}
