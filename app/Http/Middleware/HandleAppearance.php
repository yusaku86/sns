<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * クッキーからアピアランス設定を読み取りビューに共有するミドルウェア。
 */
class HandleAppearance
{
    /**
     * リクエストを処理してアピアランス設定をビューに共有する。
     *
     * @param  Request  $request  HTTPリクエスト
     * @param  Closure(Request): Response  $next  次のミドルウェア
     */
    public function handle(Request $request, Closure $next): Response
    {
        View::share('appearance', $request->cookie('appearance') ?? 'system');

        return $next($request);
    }
}
