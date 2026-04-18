<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;

/**
 * ユーザーの取得・更新を担うリポジトリインターフェース。
 */
interface UserRepositoryInterface
{
    /**
     * IDでユーザーを1件取得する。
     *
     * @param  string  $id  ユーザーID
     * @param  string|null  $authUserId  認証ユーザーID（フォロー状態の付与に使用）
     * @return User|null 見つからない場合はnull
     */
    public function findById(string $id, ?string $authUserId = null): ?User;

    /**
     * ユーザーのプロフィール情報を更新する。
     *
     * @param  string  $id  ユーザーID
     * @param  string  $name  表示名
     * @param  string|null  $bio  自己紹介文
     * @param  string|null  $headerImagePath  ヘッダー画像のストレージパス
     * @param  string|null  $profileImagePath  プロフィール画像のストレージパス
     */
    public function update(string $id, string $name, ?string $bio, ?string $headerImagePath, ?string $profileImagePath): void;
}
