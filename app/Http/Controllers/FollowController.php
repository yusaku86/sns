<?php

namespace App\Http\Controllers;

use App\Application\Follow\FollowUserUseCase;
use App\Application\Follow\UnfollowUserUseCase;
use App\Infrastructure\Eloquent\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * フォロー・アンフォロー操作を担うコントローラー。
 */
class FollowController extends Controller
{
    public function __construct(
        private FollowUserUseCase $followUser,
        private UnfollowUserUseCase $unfollowUser,
    ) {}

    /**
     * 指定ユーザーをフォローする。
     *
     * @param  Request  $request  HTTPリクエスト
     * @param  User  $user  フォロー対象ユーザー
     */
    public function store(Request $request, User $user): RedirectResponse
    {
        $this->followUser->execute($request->user()->id, $user->id);

        return back();
    }

    /**
     * 指定ユーザーのフォローを解除する。
     *
     * @param  Request  $request  HTTPリクエスト
     * @param  User  $user  フォロー解除対象ユーザー
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->unfollowUser->execute($request->user()->id, $user->id);

        return back();
    }
}
