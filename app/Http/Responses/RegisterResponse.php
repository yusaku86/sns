<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

/**
 * 登録成功時のカスタムレスポンス。チームスラッグをURLデフォルトに設定してダッシュボードへリダイレクトする。
 */
class RegisterResponse implements RegisterResponseContract
{
    /**
     * 登録成功レスポンスを生成する。
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

        URL::defaults(['current_team' => $team->slug]);

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false], 201)
            : redirect()->intended(route('dashboard'));
    }
}
