<?php

namespace App\Domain\Hashtag\Entities;

use JsonSerializable;

/**
 * ハッシュタグドメインエンティティ。投稿数の集計値を保持する。
 */
class Hashtag implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $postsCount = 0,
    ) {}

    /**
     * JSONシリアライズ用配列を返す。
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'postsCount' => $this->postsCount,
        ];
    }
}
