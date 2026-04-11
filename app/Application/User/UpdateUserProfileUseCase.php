<?php

namespace App\Application\User;

use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;

class UpdateUserProfileUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function execute(string $targetUserId, string $authUserId, string $name, ?string $bio): void
    {
        if ($targetUserId !== $authUserId) {
            throw new AuthorizationException('他のユーザーのプロフィールは編集できません。');
        }

        $this->userRepository->update($targetUserId, $name, $bio);
    }
}
