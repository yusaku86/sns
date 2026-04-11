<?php

namespace App\Application\User;

use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;

class GetUserProfileUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function execute(string $userId, ?string $authUserId = null): ?User
    {
        return $this->userRepository->findById($userId, $authUserId);
    }
}
