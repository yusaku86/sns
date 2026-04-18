<?php

namespace App\Domain\Hashtag\Repositories;

use App\Domain\Hashtag\Entities\Hashtag;

/**
 * ハッシュタグの永続化・取得を担うリポジトリインターフェース。
 */
interface HashtagRepositoryInterface
{
    /**
     * 名前でハッシュタグを取得し、存在しない場合は新規作成する。
     *
     * @param  string  $name  ハッシュタグ名（#なし）
     */
    public function findOrCreateByName(string $name): Hashtag;

    /**
     * 投稿に紐づくハッシュタグを同期する（差分更新）。
     *
     * @param  string[]  $names  ハッシュタグ名の配列
     * @param  string  $postId  投稿ID
     */
    public function syncToPost(array $names, string $postId): void;

    /**
     * 投稿数の多いハッシュタグを上位 $limit 件返す。
     *
     * @param  int  $limit  取得件数
     * @return Hashtag[]
     */
    public function getTrending(int $limit = 5): array;
}
