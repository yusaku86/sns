<?php

namespace App\Http\Controllers;

use App\Application\Explore\GetExploreUseCase;
use App\Application\Post\SearchPostsUseCase;
use App\Http\Presenters\PostPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 探索ページを担うコントローラー。
 */
class ExploreController extends Controller
{
    public function __construct(
        private GetExploreUseCase $getExplore,
        private SearchPostsUseCase $searchPosts,
    ) {}

    /**
     * 探索ページを表示する。qパラメータがあれば投稿検索、なければ全体一覧を返す。
     *
     * @param  Request  $request  HTTPリクエスト
     * @return Response Inertiaレスポンス
     */
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'cursor' => ['nullable', 'string', 'date_format:Y-m-d\TH:i:sP'],
            'q' => ['nullable', 'string', 'min:1', 'max:100'],
        ]);

        $query = trim($validated['q'] ?? '');
        $cursor = $validated['cursor'] ?? null;
        $authUserId = $request->user()?->id;

        if ($query !== '') {
            $result = $this->searchPosts->execute($query, $authUserId, $cursor);
        } else {
            $result = $this->getExplore->execute($authUserId, $cursor);
        }

        return Inertia::render('explore', [
            'posts' => PostPresenter::collection($result['posts']),
            'nextCursor' => $result['nextCursor'],
            'hasMore' => $result['hasMore'],
            'query' => $query,
        ]);
    }
}
