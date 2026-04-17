<?php

namespace App\Http\Controllers;

use App\Application\Post\CreatePostUseCase;
use App\Application\Post\DeletePostUseCase;
use App\Application\Post\GetPostUseCase;
use App\Http\Requests\StorePostRequest;
use App\Infrastructure\Eloquent\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function __construct(
        private CreatePostUseCase $createPost,
        private DeletePostUseCase $deletePost,
        private GetPostUseCase $getPost,
    ) {}

    public function show(Request $request, Post $post): Response
    {
        ['post' => $postEntity, 'replies' => $replies] = $this->getPost->execute(
            postId: $post->id,
            authUserId: $request->user()?->id,
        );

        return Inertia::render('posts/show', [
            'post' => $postEntity,
            'replies' => $replies,
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $imagePaths = collect($request->file('images', []))
            ->map(fn (UploadedFile $file) => $file->storeAs(
                'post_images',
                Str::uuid().'.'.$file->extension(),
                'local',
            ))
            ->filter()
            ->values()
            ->all();

        $this->createPost->execute(
            userId: $request->user()->id,
            userName: $request->user()->name,
            userHandle: $request->user()->handle,
            content: $request->validated('content') ?? '',
            imagePaths: $imagePaths,
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
