<?php

namespace App\Domain\User\Entities;

use JsonSerializable;

/**
 * ユーザードメインエンティティ。プロフィール情報とフォロー集計を保持する。
 */
class User implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $handle,
        public readonly string $email,
        public readonly ?string $bio,
        public readonly ?string $headerImageUrl,
        public readonly ?string $profileImageUrl,
        public readonly int $postsCount,
        public readonly int $followersCount,
        public readonly int $followingCount,
        public readonly bool $isFollowedByAuthUser,
        public readonly ?\DateTimeImmutable $createdAt = null,
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
            'bio' => $this->bio,
            'headerImageUrl' => $this->headerImageUrl,
            'profileImageUrl' => $this->profileImageUrl,
            'postsCount' => $this->postsCount,
            'followersCount' => $this->followersCount,
            'followingCount' => $this->followingCount,
            'isFollowedByAuthUser' => $this->isFollowedByAuthUser,
            'createdAt' => $this->createdAt?->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
