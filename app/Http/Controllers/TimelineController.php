<?php

namespace App\Http\Controllers;

use App\Application\Timeline\GetTimelineUseCase;
use App\Http\Presenters\PostPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * タイムライン一覧ページを担うコントローラー。
 */
class TimelineController extends Controller
{
    public function __construct(
        private GetTimelineUseCase $getTimeline,
    ) {}

    /**
     * タイムラインページを表示する。
     *
     * @param  Request  $request  HTTPリクエスト
     * @return Response Inertiaレスポンス
     */
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'cursor' => ['nullable', 'string', 'date_format:Y-m-d\TH:i:sP'],
        ]);

        $result = $this->getTimeline->execute($request->user()->id, $validated['cursor'] ?? null);

        return Inertia::render('timeline', [
            'posts' => PostPresenter::collection($result['posts']),
            'nextCursor' => $result['nextCursor'],
            'hasMore' => $result['hasMore'],
        ]);
    }
}
