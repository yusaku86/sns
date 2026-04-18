<?php

namespace App\Domain\Follow\Repositories;

use App\Domain\Follow\Entities\FollowUser;

/**
 * フォロー関係の永続化・取得を担うリポジトリインターフェース。
 */
interface FollowRepositoryInterface
{
    /**
     * フォロー関係が存在するか確認する。
     *
     * @param  string  $followerId  フォローするユーザーID
     * @param  string  $followingId  フォローされるユーザーID
     * @return bool フォロー済みの場合true
     */
    public function exists(string $followerId, string $followingId): bool;

    /**
     * フォロー関係を保存する。
     *
     * @param  string  $followerId  フォローするユーザーID
     * @param  string  $followingId  フォローされるユーザーID
     */
    public function save(string $followerId, string $followingId): void;

    /**
     * フォロー関係を削除する。
     *
     * @param  string  $followerId  フォローするユーザーID
     * @param  string  $followingId  フォローされるユーザーID
     */
    public function delete(string $followerId, string $followingId): void;

    /**
     * 指定ユーザーのフォロワー一覧を返す。
     *
     * @param  string  $userId  対象ユーザーID
     * @param  string|null  $authUserId  認証ユーザーID（フォロー状態の付与に使用）
     * @return FollowUser[]
     */
    public function getFollowers(string $userId, ?string $authUserId = null): array;

    /**
     * 指定ユーザーがフォロー中のユーザー一覧を返す。
     *
     * @param  string  $userId  対象ユーザーID
     * @param  string|null  $authUserId  認証ユーザーID（フォロー状態の付与に使用）
     * @return FollowUser[]
     */
    public function getFollowing(string $userId, ?string $authUserId = null): array;

    /**
     * フォロー中のユーザーがフォローしているユーザーのうち、
     * 自分未フォロー・自分自身を除いてフォロワー数の多い順に返す。
     *
     * @param  string  $authUserId  認証ユーザーID
     * @param  int  $limit  取得件数
     * @return FollowUser[]
     */
    public function getSuggestedUsers(string $authUserId, int $limit): array;
}
