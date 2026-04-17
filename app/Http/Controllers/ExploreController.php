<?php

namespace App\Http\Controllers;

use App\Application\Explore\GetExploreUseCase;
use App\Http\Presenters\PostPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExploreController extends Controller
{
    public function __construct(
        private GetExploreUseCase $getExplore,
    ) {}

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'cursor' => ['nullable', 'string', 'date_format:Y-m-d\TH:i:sP'],
        ]);

        $result = $this->getExplore->execute($request->user()?->id, $validated['cursor'] ?? null);

        return Inertia::render('explore', [
            'posts' => PostPresenter::collection($result['posts']),
            'nextCursor' => $result['nextCursor'],
            'hasMore' => $result['hasMore'],
        ]);
    }
}
