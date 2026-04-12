<?php

namespace App\Domain\Reply\Repositories;

use App\Domain\Reply\Entities\Reply;

interface ReplyRepositoryInterface
{
    /** @return Reply[] */
    public function getByPostId(string $postId): array;

    public function save(Reply $reply): void;
}
