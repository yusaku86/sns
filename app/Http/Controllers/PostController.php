<?php

namespace App\Http\Controllers;

use App\Application\Post\CreatePostUseCase;
use App\Application\Post\DeletePostUseCase;
use App\Application\Post\GetPostUseCase;
use App\Application\Post\PostImageStorageInterface;
use App\Http\Presenters\PostPresenter;
use App\Http\Requests\StorePostRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function __construct(
        private CreatePostUseCase $createPost,
        private DeletePostUseCase $deletePost,
        private GetPostUseCase $getPost,
        private PostImageStorageInterface $imageStorage,
    ) {}

    public function show(Request $request, string $post): Response
    {
        ['post' => $postEntity, 'replies' => $replies] = $this->getPost->execute(
            postId: $post,
            authUserId: $request->user()?->id,
        );

        return Inertia::render('posts/show', [
            'post' => PostPresenter::toArray($postEntity),
            'replies' => $replies,
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $imagePaths = $this->imageStorage->storeAll($request->file('images', []));

        $this->createPost->execute(
            postId: (string) Str::uuid(),
            userId: $request->user()->id,
            userName: $request->user()->name,
            userHandle: $request->user()->handle,
            content: $request->validated('content') ?? '',
            imagePaths: $imagePaths,
        );

        return back();
    }

    public function destroy(Request $request, string $post): RedirectResponse
    {
        $this->deletePost->execute(
            postId: $post,
            authUserId: $request->user()->id,
        );

        return back();
    }
}
