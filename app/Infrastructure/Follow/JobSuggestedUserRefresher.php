<?php

namespace App\Infrastructure\Follow;

use App\Application\Follow\SuggestedUserRefresherInterface;
use App\Infrastructure\Jobs\ComputeSuggestedUsersJob;

/**
 * キュージョブ経由でおすすめユーザーの再計算を依頼する実装。
 */
class JobSuggestedUserRefresher implements SuggestedUserRefresherInterface
{
    /**
     * {@inheritdoc}
     */
    public function refresh(string $userId, int $limit): void
    {
        ComputeSuggestedUsersJob::dispatch($userId, $limit);
    }
}
