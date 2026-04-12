<?php

namespace App\Domain\Hashtag\Entities;

class Hashtag
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {}
}
