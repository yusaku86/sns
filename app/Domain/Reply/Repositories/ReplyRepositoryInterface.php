<?php

namespace App\Domain\Reply\Repositories;

use App\Domain\Reply\Entities\Reply;

/**
 * リプライの永続化・取得を担うリポジトリインターフェース。
 */
interface ReplyRepositoryInterface
{
    /**
     * 投稿IDに紐づくリプライ一覧を返す。
     *
     * @param  string  $postId  投稿ID
     * @return Reply[]
     */
    public function getByPostId(string $postId): array;

    /**
     * ユーザーが投稿したリプライ一覧を元投稿の文脈付きで返す。
     *
     * @param  string  $userId  ユーザーID
     * @param  int  $limit  取得件数
     * @return Reply[]
     */
    public function getByUserId(string $userId, int $limit = 20): array;

    /**
     * リプライを保存する（新規作成）。
     *
     * @param  Reply  $reply  保存するリプライエンティティ
     */
    public function save(Reply $reply): void;
}
