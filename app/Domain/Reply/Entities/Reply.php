<?php

namespace App\Domain\Reply\Entities;

use DateTimeImmutable;
use JsonSerializable;

/**
 * リプライドメインエンティティ。元投稿の文脈情報をオプションで保持する。
 */
class Reply implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $postId,
        public readonly string $userId,
        public readonly string $userName,
        public readonly string $userHandle,
        public readonly string $content,
        public readonly DateTimeImmutable $createdAt,
        public readonly ?string $postContent = null,
        public readonly ?string $postUserName = null,
        public readonly ?string $postUserHandle = null,
        public readonly ?string $userProfileImageUrl = null,
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
            'postId' => $this->postId,
            'userId' => $this->userId,
            'userName' => $this->userName,
            'userHandle' => $this->userHandle,
            'content' => $this->content,
            'createdAt' => $this->createdAt->format('Y/m/d H:i'),
            'postContent' => $this->postContent,
            'postUserName' => $this->postUserName,
            'postUserHandle' => $this->postUserHandle,
            'userProfileImageUrl' => $this->userProfileImageUrl,
        ];
    }
}
