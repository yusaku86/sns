<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * チームベースルートのURLデフォルトパラメーターを設定するミドルウェア。
 */
class SetTeamUrlDefaults
{
    /**
     * チームスラッグをURLデフォルトパラメーターとして設定する。
     *
     * @param  Request  $request  HTTPリクエスト
     * @param  Closure(Request): Response  $next  次のミドルウェア
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($currentTeam = $request->user()?->currentTeam) {
            URL::defaults([
                'current_team' => $currentTeam->slug,
                'team' => $currentTeam->slug,
            ]);
        }

        return $next($request);
    }
}
