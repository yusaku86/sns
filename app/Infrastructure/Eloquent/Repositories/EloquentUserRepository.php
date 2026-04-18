<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\User\Entities\User as UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Eloquent\Models\User as UserModel;
use Illuminate\Support\Facades\Storage;

/**
 * Eloquentを使ったユーザーリポジトリの実装。
 */
class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findById(string $id, ?string $authUserId = null): ?UserEntity
    {
        $model = UserModel::withCount(['posts', 'followers', 'followings'])
            ->find($id);

        if (! $model) {
            return null;
        }

        $isFollowed = $authUserId
            ? $model->followers()->where('follower_id', $authUserId)->exists()
            : false;

        return $this->toEntity($model, $isFollowed);
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $id, string $name, ?string $bio, ?string $headerImagePath, ?string $profileImagePath): void
    {
        $data = ['name' => $name, 'bio' => $bio];

        if ($headerImagePath !== null) {
            $data['header_image'] = $headerImagePath;
        }

        if ($profileImagePath !== null) {
            $data['profile_image'] = $profileImagePath;
        }

        UserModel::where('id', $id)->update($data);
    }

    /**
     * UserモデルからUserエンティティを生成する。
     *
     * @param  UserModel  $model  ユーザーモデル
     * @param  bool  $isFollowedByAuthUser  認証ユーザーにフォローされているか
     */
    private function toEntity(UserModel $model, bool $isFollowedByAuthUser): UserEntity
    {
        return new UserEntity(
            id: $model->id,
            name: $model->name,
            handle: $model->handle,
            email: $model->email,
            bio: $model->bio,
            headerImageUrl: $model->header_image ? Storage::disk('public')->url($model->header_image) : null,
            profileImageUrl: $model->profile_image ? Storage::disk('public')->url($model->profile_image) : null,
            postsCount: $model->posts_count,
            followersCount: $model->followers_count,
            followingCount: $model->followings_count,
            isFollowedByAuthUser: $isFollowedByAuthUser,
            createdAt: $model->created_at ? new \DateTimeImmutable($model->created_at) : null,
        );
    }
}
