<?php

namespace App\Http\Controllers;

use App\Application\Retweet\RetweetPostUseCase;
use App\Application\Retweet\UnretweetPostUseCase;
use App\Infrastructure\Eloquent\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * リツイート・リツイート取り消し操作を担うコントローラー。
 */
class RetweetController extends Controller
{
    public function __construct(
        private RetweetPostUseCase $retweetPost,
        private UnretweetPostUseCase $unretweetPost,
    ) {}

    /**
     * 投稿をリツイートする。
     *
     * @param  Request  $request  HTTPリクエスト
     * @param  Post  $post  リツイート対象の投稿
     */
    public function store(Request $request, Post $post): RedirectResponse
    {
        $this->retweetPost->execute($request->user()->id, $post->id);

        return back();
    }

    /**
     * リツイートを取り消す。
     *
     * @param  Request  $request  HTTPリクエスト
     * @param  Post  $post  リツイート取り消し対象の投稿
     */
    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $this->unretweetPost->execute($request->user()->id, $post->id);

        return back();
    }
}
