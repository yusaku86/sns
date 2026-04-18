<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

/**
 * ログイン成功時のカスタムレスポンス。ダッシュボードへリダイレクトする。
 */
class LoginResponse implements LoginResponseContract
{
    /**
     * ログイン成功レスポンスを生成する。
     *
     * @param  Request  $request  HTTPリクエスト
     */
    public function toResponse($request): Response
    {
        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false], 200)
            : redirect()->intended(route('dashboard'));
    }
}
