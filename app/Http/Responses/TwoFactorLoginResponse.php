<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

/**
 * 2要素認証ログイン成功時のカスタムレスポンス。チームのダッシュボードへリダイレクトする。
 */
class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    /**
     * 2要素認証ログイン成功レスポンスを生成する。
     *
     * @param  Request  $request  HTTPリクエスト
     */
    public function toResponse($request): Response
    {
        $user = $request->user();
        $team = $user->currentTeam ?? $user?->personalTeam();

        if (! $team) {
            abort(403);
        }

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false], 200)
            : redirect()->intended("/{$team->slug}/dashboard");
    }
}
