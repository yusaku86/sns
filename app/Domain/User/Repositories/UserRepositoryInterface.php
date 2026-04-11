<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;

interface UserRepositoryInterface
{
    public function findById(string $id, ?string $authUserId = null): ?User;

    public function update(string $id, string $name, ?string $bio): void;
}
