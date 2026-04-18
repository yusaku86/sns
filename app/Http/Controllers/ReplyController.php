<?php

namespace App\Http\Controllers;

use App\Application\Reply\CreateReplyUseCase;
use App\Http\Requests\StoreReplyRequest;
use App\Infrastructure\Eloquent\Models\Post;
use Illuminate\Http\RedirectResponse;

/**
 * リプライの作成を担うコントローラー。
 */
class ReplyController extends Controller
{
    public function __construct(
        private CreateReplyUseCase $createReply,
    ) {}

    /**
     * リプライを投稿する。
     *
     * @param  StoreReplyRequest  $request  バリデーション済みリクエスト
     * @param  Post  $post  リプライ先の投稿
     */
    public function store(StoreReplyRequest $request, Post $post): RedirectResponse
    {
        $this->createReply->execute(
            postId: $post->id,
            userId: $request->user()->id,
            userName: $request->user()->name,
            userHandle: $request->user()->handle,
            content: $request->validated('content'),
        );

        return back();
    }
}
