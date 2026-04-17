<?php

namespace App\Domain\Post\Entities;

use DateTimeImmutable;
use JsonSerializable;

class Post implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $userName,
        public readonly string $userHandle,
        public readonly string $content,
        public readonly DateTimeImmutable $createdAt,
        public readonly int $likesCount,
        public readonly bool $likedByAuthUser,
        public readonly int $repliesCount = 0,
        public readonly int $retweetsCount = 0,
        public readonly bool $retweetedByAuthUser = false,
        public readonly ?string $retweetId = null,
        public readonly ?string $retweetedByUserName = null,
        public readonly ?string $retweetedByUserHandle = null,
        public readonly ?DateTimeImmutable $retweetedAt = null,
        /** @var string[] */
        public readonly array $hashtags = [],
        public readonly ?string $userProfileImageUrl = null,
        /** @var PostImage[] */
        public readonly array $images = [],
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'userName' => $this->userName,
            'userHandle' => $this->userHandle,
            'content' => $this->content,
            'createdAt' => $this->createdAt->format('Y/m/d H:i'),
            'likesCount' => $this->likesCount,
            'likedByAuthUser' => $this->likedByAuthUser,
            'repliesCount' => $this->repliesCount,
            'retweetsCount' => $this->retweetsCount,
            'retweetedByAuthUser' => $this->retweetedByAuthUser,
            'retweetId' => $this->retweetId,
            'retweetedByUserName' => $this->retweetedByUserName,
            'retweetedByUserHandle' => $this->retweetedByUserHandle,
            'hashtags' => $this->hashtags,
            'userProfileImageUrl' => $this->userProfileImageUrl,
            'images' => array_map(fn (PostImage $img) => [
                'id' => $img->id,
                'url' => $img->url,
                'order' => $img->order,
            ], $this->images),
        ];
    }
}
