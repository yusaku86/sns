<?php

namespace App\Domain\Reply\Repositories;

use App\Domain\Reply\Entities\Reply;

interface ReplyRepositoryInterface
{
    /** @return Reply[] */
    public function getByPostId(string $postId): array;

    /**
     * ユーザーが投稿したリプライ一覧（元投稿の文脈付き）
     *
     * @return Reply[]
     */
    public function getByUserId(string $userId, int $limit = 20): array;

    public function save(Reply $reply): void;
}
