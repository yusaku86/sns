<?php

namespace App\Domain\Follow\Entities;

use JsonSerializable;

/**
 * フォロワー／フォロー中ユーザーの表示用エンティティ。
 */
class FollowUser implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $handle,
        public readonly ?string $profileImageUrl,
        public readonly bool $isFollowedByAuthUser,
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
            'handle' => $this->handle,
            'profileImageUrl' => $this->profileImageUrl,
            'isFollowedByAuthUser' => $this->isFollowedByAuthUser,
        ];
    }
}
