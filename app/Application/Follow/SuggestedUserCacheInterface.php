<?php

namespace App\Application\Follow;

use App\Domain\Follow\Entities\FollowUser;

/**
 * おすすめユーザーのキャッシュを管理するインターフェース。
 */
interface SuggestedUserCacheInterface
{
    /**
     * キャッシュされたおすすめユーザーを返す。
     * キャッシュがない場合は null を返す。
     *
     * @param  string  $userId  認証ユーザーID
     * @return FollowUser[]|null
     */
    public function get(string $userId): ?array;

    /**
     * おすすめユーザーをキャッシュに保存する。
     *
     * @param  string  $userId  認証ユーザーID
     * @param  FollowUser[]  $users  保存するおすすめユーザー
     */
    public function put(string $userId, array $users): void;

    /**
     * キャッシュがフレッシュ（バックグラウンド再計算が不要）かどうかを返す。
     * TTL の具体値は実装クラスが決定する。
     *
     * @param  string  $userId  認証ユーザーID
     */
    public function isFresh(string $userId): bool;
}
