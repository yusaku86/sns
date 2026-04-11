<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\User\Entities\User as UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Eloquent\Models\User as UserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
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

    public function update(string $id, string $name, ?string $bio): void
    {
        UserModel::where('id', $id)->update(['name' => $name, 'bio' => $bio]);
    }

    private function toEntity(UserModel $model, bool $isFollowedByAuthUser): UserEntity
    {
        return new UserEntity(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            bio: $model->bio,
            postsCount: $model->posts_count,
            followersCount: $model->followers_count,
            followingCount: $model->followings_count,
            isFollowedByAuthUser: $isFollowedByAuthUser,
        );
    }
}
