<?php

namespace App\Http\Controllers;

use App\Application\Post\CreatePostUseCase;
use App\Application\Post\DeletePostUseCase;
use App\Http\Requests\StorePostRequest;
use App\Infrastructure\Eloquent\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private CreatePostUseCase $createPost,
        private DeletePostUseCase $deletePost,
    ) {}

    public function store(StorePostRequest $request): RedirectResponse
    {
        $this->createPost->execute(
            userId: $request->user()->id,
            userName: $request->user()->name,
            content: $request->validated('content'),
        );

        return back();
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $this->deletePost->execute(
            postId: $post->id,
            authUserId: $request->user()->id,
        );

        return back();
    }
}
