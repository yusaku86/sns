<?php

namespace App\Application\User;

use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;

/**
 * ユーザープロフィールを更新するユースケース。本人のみ更新を許可する。
 */
class UpdateUserProfileUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    /**
     * プロフィールを更新する。
     *
     * @param  string  $targetUserId  更新対象のユーザーID
     * @param  string  $authUserId  操作を行う認証ユーザーID
     * @param  string  $name  表示名
     * @param  string|null  $bio  自己紹介文
     * @param  UploadedFile|null  $headerImage  ヘッダー画像ファイル
     * @param  UploadedFile|null  $profileImage  プロフィール画像ファイル
     *
     * @throws AuthorizationException 本人以外が更新しようとした場合
     */
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
