<?php

namespace App\Domain\Hashtag\Repositories;

use App\Domain\Hashtag\Entities\Hashtag;

interface HashtagRepositoryInterface
{
    public function findOrCreateByName(string $name): Hashtag;

    /** @param string[] $names */
    public function syncToPost(array $names, string $postId): void;

    /**
     * 投稿数の多いハッシュタグを上位 $limit 件返す
     *
     * @return Hashtag[]
     */
    public function getTrending(int $limit = 5): array;
}
