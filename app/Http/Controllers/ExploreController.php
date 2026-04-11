<?php

namespace App\Http\Controllers;

use App\Application\Explore\GetExploreUseCase;
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
        $posts = $this->getExplore->execute($request->user()?->id);

        return Inertia::render('explore', [
            'posts' => $posts,
        ]);
    }
}
