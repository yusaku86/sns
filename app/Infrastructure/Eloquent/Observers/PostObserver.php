<?php

namespace App\Infrastructure\Eloquent\Observers;

use App\Infrastructure\Eloquent\Models\Post;
use App\Jobs\UpdateTrendingHashtagsJob;

class PostObserver
{
    /**
     * 投稿作成後にトレンドキャッシュを非同期で再構築する。
     * ハッシュタグとの紐付けは createPost 後に sync されるため、
     * created ではなく saved（またはハッシュタグ sync 後）でも良いが、
     * ShouldBeUnique により 60 秒以内の重複実行は抑制される。
     */
    public function created(Post $post): void
    {
        UpdateTrendingHashtagsJob::dispatch();
    }

    /**
     * 投稿削除後にトレンドキャッシュを非同期で再構築する。
     */
    public function deleted(Post $post): void
    {
        UpdateTrendingHashtagsJob::dispatch();
    }
}
