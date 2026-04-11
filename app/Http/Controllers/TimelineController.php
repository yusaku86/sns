<?php

namespace App\Http\Controllers;

use App\Application\Timeline\GetTimelineUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TimelineController extends Controller
{
    public function __construct(
        private GetTimelineUseCase $getTimeline,
    ) {}

    public function index(Request $request): Response
    {
        $posts = $this->getTimeline->execute($request->user()->id);

        return Inertia::render('timeline', [
            'posts' => $posts,
        ]);
    }
}
