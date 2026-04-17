<?php

namespace App\Http\Controllers;

use App\Application\Hashtag\GetHashtagPostsUseCase;
use App\Http\Presenters\PostPresenter;
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
        $validated = $request->validate([
            'cursor' => ['nullable', 'string', 'date_format:Y-m-d\TH:i:sP'],
        ]);

        $result = $this->getHashtagPosts->execute(
            hashtagName: $hashtag,
            authUserId: $request->user()?->id,
            cursor: $validated['cursor'] ?? null,
        );

        return Inertia::render('hashtags/show', [
            'hashtag' => $hashtag,
            'posts' => PostPresenter::collection($result['posts']),
            'nextCursor' => $result['nextCursor'],
            'hasMore' => $result['hasMore'],
        ]);
    }
}
