<?php

namespace App\Application\Follow;

/**
 * おすすめユーザーのバックグラウンド再計算を依頼するインターフェース。
 * UseCase がジョブの実装詳細を知らないようにするための抽象。
 */
interface SuggestedUserRefresherInterface
{
    /**
     * バックグラウンドでおすすめユーザーを再計算するよう依頼する。
     *
     * @param  string  $userId  再計算対象の認証ユーザーID
     * @param  int  $limit  取得件数
     */
    public function refresh(string $userId, int $limit): void;
}
