<?php

namespace App\Domain\Hashtag\Entities;

use JsonSerializable;

class Hashtag implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $postsCount = 0,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'postsCount' => $this->postsCount,
        ];
    }
}
