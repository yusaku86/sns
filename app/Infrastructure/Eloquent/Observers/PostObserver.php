<?php

namespace App\Infrastructure\Eloquent\Observers;

use App\Infrastructure\Eloquent\Models\Post;
use App\Jobs\UpdateTrendingHashtagsJob;
use Illuminate\Support\Facades\Storage;

/**
 * 投稿モデルのライフサイクルイベントを処理するオブザーバー。
 */
class PostObserver
{
    /**
     * 投稿作成後にトレンドキャッシュを非同期で再構築する。
     * ハッシュタグとの紐付けはcreatePost後にsyncされるため、
     * ShouldBeUniqueにより60秒以内の重複実行は抑制される。
     *
     * @param  Post  $post  作成された投稿モデル
     */
    public function created(Post $post): void
    {
        UpdateTrendingHashtagsJob::dispatch();
    }

    /**
     * 投稿削除前に添付画像ファイルをストレージから削除する。
     *
     * @param  Post  $post  削除対象の投稿モデル
     */
    public function deleting(Post $post): void
    {
        foreach ($post->images as $image) {
            Storage::disk('local')->delete($image->path);
        }
    }

    /**
     * 投稿削除後にトレンドキャッシュを非同期で再構築する。
     *
     * @param  Post  $post  削除された投稿モデル
     */
    public function deleted(Post $post): void
    {
        UpdateTrendingHashtagsJob::dispatch();
    }
}
