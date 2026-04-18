<?php

namespace App\Http\Controllers;

use App\Application\Like\LikePostUseCase;
use App\Application\Like\UnlikePostUseCase;
use App\Infrastructure\Eloquent\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * いいね・いいね取り消し操作を担うコントローラー。
 */
class LikeController extends Controller
{
    public function __construct(
        private LikePostUseCase $likePost,
        private UnlikePostUseCase $unlikePost,
    ) {}

    /**
     * 投稿にいいねする。
     *
     * @param  Request  $request  HTTPリクエスト
     * @param  Post  $post  いいね対象の投稿
     */
    public function store(Request $request, Post $post): RedirectResponse
    {
        $this->likePost->execute($request->user()->id, $post->id);

        return back();
    }

    /**
     * 投稿のいいねを取り消す。
     *
     * @param  Request  $request  HTTPリクエスト
     * @param  Post  $post  いいね取り消し対象の投稿
     */
    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $this->unlikePost->execute($request->user()->id, $post->id);

        return back();
    }
}
