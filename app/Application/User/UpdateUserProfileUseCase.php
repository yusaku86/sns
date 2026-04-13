<?php

namespace App\Application\User;

use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;

class UpdateUserProfileUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function execute(
        string $targetUserId,
        string $authUserId,
        string $name,
        ?string $bio,
        ?UploadedFile $headerImage = null,
        ?UploadedFile $profileImage = null,
    ): void {
        if ($targetUserId !== $authUserId) {
            throw new AuthorizationException('他のユーザーのプロフィールは編集できません。');
        }

        $headerImagePath = $headerImage
            ? $headerImage->store('header_images', 'public')
            : null;

        $profileImagePath = $profileImage
            ? $profileImage->store('profile_images', 'public')
            : null;

        $this->userRepository->update($targetUserId, $name, $bio, $headerImagePath, $profileImagePath);
    }
}
