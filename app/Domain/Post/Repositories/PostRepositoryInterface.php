<?php

namespace App\Domain\Post\Repositories;

use App\Domain\Post\Entities\Post;

/**
 * 投稿の永続化・取得を担うリポジトリインターフェース。
 */
interface PostRepositoryInterface
{
    /**
     * IDで投稿を1件取得する。
     *
     * @param  string  $id  投稿ID
     * @param  string|null  $authUserId  認証ユーザーID（いいね・リツイート状態の付与に使用）
     * @return Post|null 見つからない場合はnull
     */
    public function findById(string $id, ?string $authUserId = null): ?Post;

    /**
     * 指定ユーザーのタイムライン投稿一覧を返す。
     *
     * @param  string  $userId  対象ユーザーID
     * @param  int  $limit  取得件数
     * @param  string|null  $cursor  ページネーションカーソル
     * @return Post[]
     */
    public function getTimeline(string $userId, int $limit = 20, ?string $cursor = null): array;

    /**
     * 全投稿一覧を返す（探索ページ用）。
     *
     * @param  string|null  $authUserId  認証ユーザーID
     * @param  int  $limit  取得件数
     * @param  string|null  $cursor  ページネーションカーソル
     * @return Post[]
     */
    public function getAll(?string $authUserId = null, int $limit = 20, ?string $cursor = null): array;

    /**
     * 投稿を保存する（新規作成）。
     *
     * @param  Post  $post  保存する投稿エンティティ
     */
    public function save(Post $post): void;

    /**
     * 指定IDの投稿を削除する。
     *
     * @param  string  $id  投稿ID
     */
    public function delete(string $id): void;

    /**
     * ハッシュタグ名に紐づく投稿一覧を返す。
     *
     * @param  string  $hashtagName  ハッシュタグ名
     * @param  string|null  $authUserId  認証ユーザーID
     * @param  int  $limit  取得件数
     * @param  string|null  $cursor  ページネーションカーソル
     * @return Post[]
     */
    public function getByHashtag(string $hashtagName, ?string $authUserId = null, int $limit = 20, ?string $cursor = null): array;

    /**
     * 指定ユーザーの投稿一覧をリツイートと混合して返す（プロフィール投稿タブ用）。
     * 自分の投稿とリツイートを日時降順でマージし、カーソルページネーションで返す。
     *
     * @param  string  $userId  対象ユーザーID
     * @param  string|null  $authUserId  認証ユーザーID
     * @param  int  $limit  取得件数
     * @param  string|null  $cursor  ページネーションカーソル
     * @return Post[]
     */
    public function getByUserId(string $userId, ?string $authUserId = null, int $limit = 20, ?string $cursor = null): array;

    /**
     * キーワードで投稿本文を部分一致検索する（リツイートは除外）。
     *
     * @param  string  $keyword  検索キーワード
     * @param  string|null  $authUserId  認証ユーザーID
     * @param  int  $limit  取得件数
     * @param  string|null  $cursor  ページネーションカーソル
     * @return Post[]
     */
    public function searchByKeyword(string $keyword, ?string $authUserId = null, int $limit = 20, ?string $cursor = null): array;
}
