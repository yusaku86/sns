<?php

namespace App\Http\Controllers;

use App\Application\Like\LikePostUseCase;
use App\Application\Like\UnlikePostUseCase;
use App\Infrastructure\Eloquent\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function __construct(
        private LikePostUseCase $likePost,
        private UnlikePostUseCase $unlikePost,
    ) {}

    public function store(Request $request, Post $post): RedirectResponse
    {
        $this->likePost->execute($request->user()->id, $post->id);

        return back();
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $this->unlikePost->execute($request->user()->id, $post->id);

        return back();
    }
}
