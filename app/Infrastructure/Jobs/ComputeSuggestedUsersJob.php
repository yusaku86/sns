<?php

namespace App\Infrastructure\Jobs;

use App\Application\Follow\ComputeSuggestedUsersUseCase;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * おすすめユーザーをバックグラウンドで計算・キャッシュするジョブ。
 * ShouldBeUnique により同一ユーザーへの重複ディスパッチを防ぐ。
 */
class ComputeSuggestedUsersJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /** ユニーク制約の有効期間（秒）。フレッシュTTLと合わせる。 */
    public int $uniqueFor = 3600;

    public function __construct(
        public readonly string $userId,
        public readonly int $limit,
    ) {}

    /**
     * ジョブのユニークキーを返す。同一ユーザーへの重複ディスパッチを防ぐ。
     */
    public function uniqueId(): string
    {
        return $this->userId;
    }

    /**
     * ジョブを実行する。
     *
     * @param  ComputeSuggestedUsersUseCase  $useCase  おすすめユーザー計算ユースケース
     */
    public function handle(ComputeSuggestedUsersUseCase $useCase): void
    {
        $useCase->execute($this->userId, $this->limit);
    }
}
