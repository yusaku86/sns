<?php

namespace App\Http\Controllers;

use App\Application\Hashtag\GetHashtagPostsUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HashtagController extends Controller
{
    public function __construct(
        private GetHashtagPostsUseCase $getHashtagPosts,
    ) {}

    public function show(Request $request, string $hashtag): Response
    {
        $posts = $this->getHashtagPosts->execute(
            hashtagName: $hashtag,
            authUserId: $request->user()?->id,
        );

        return Inertia::render('hashtags/show', [
            'hashtag' => $hashtag,
            'posts' => $posts,
        ]);
    }
}
