<?php

namespace App\Http\Controllers;

use App\Application\Retweet\RetweetPostUseCase;
use App\Application\Retweet\UnretweetPostUseCase;
use App\Infrastructure\Eloquent\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RetweetController extends Controller
{
    public function __construct(
        private RetweetPostUseCase $retweetPost,
        private UnretweetPostUseCase $unretweetPost,
    ) {}

    public function store(Request $request, Post $post): RedirectResponse
    {
        $this->retweetPost->execute($request->user()->id, $post->id);

        return back();
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $this->unretweetPost->execute($request->user()->id, $post->id);

        return back();
    }
}
