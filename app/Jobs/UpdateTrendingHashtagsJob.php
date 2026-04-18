<?php

namespace App\Jobs;

use App\Application\Hashtag\GetTrendingHashtagsUseCase;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * トレンドハッシュタグキャッシュを非同期で再構築するジョブ。
 * ShouldBeUniqueにより60秒以内の重複ディスパッチは抑制される。
 */
class UpdateTrendingHashtagsJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * キュー内での一意性保持時間（秒）。
     * この間に同じジョブが積まれても重複実行しない。
     */
    public int $uniqueFor = 60;

    /**
     * ジョブを実行してトレンドキャッシュを再構築する。
     *
     * @param  GetTrendingHashtagsUseCase  $useCase  トレンドハッシュタグ取得ユースケース
     */
    public function handle(GetTrendingHashtagsUseCase $useCase): void
    {
        $useCase->refresh();
    }
}
