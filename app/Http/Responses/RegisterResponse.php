<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

/**
 * 登録成功時のカスタムレスポンス。ダッシュボードへリダイレクトする。
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
        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false], 201)
            : redirect()->intended(route('dashboard'));
    }
}
