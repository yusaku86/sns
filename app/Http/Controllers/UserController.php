<?php

namespace App\Http\Controllers;

use App\Application\User\GetUserProfileUseCase;
use App\Application\User\UpdateUserProfileUseCase;
use App\Http\Presenters\PostPresenter;
use App\Http\Requests\UpdateProfileRequest;
use App\Infrastructure\Eloquent\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * ユーザープロフィールの表示・更新を担うコントローラー。
 */
class UserController extends Controller
{
    public function __construct(
        private GetUserProfileUseCase $getUserProfile,
        private UpdateUserProfileUseCase $updateUserProfile,
    ) {}

    /**
     * ユーザープロフィールページを表示する。
     *
     * @param  Request  $request  HTTPリクエスト
     * @param  User  $user  ルートモデルバインディングで解決したユーザー
     * @return Response Inertiaレスポンス
     */
    public function show(Request $request, User $user): Response
    {
        $validated = $request->validate([
            'cursor' => ['nullable', 'string', 'date_format:Y-m-d\TH:i:sP'],
        ]);

        $result = $this->getUserProfile->execute(
            userId: $user->id,
            authUserId: $request->user()?->id,
            cursor: $validated['cursor'] ?? null,
        );

        abort_if(! $result, 404);

        return Inertia::render('users/show', [
            'user' => $result['user'],
            'posts' => PostPresenter::collection($result['posts']),
            'nextCursor' => $result['nextCursor'],
            'hasMore' => $result['hasMore'],
            'replies' => $result['replies'],
            'likedPosts' => PostPresenter::collection($result['likedPosts']),
            'followers' => Inertia::defer(fn () => $result['followers']),
            'following' => Inertia::defer(fn () => $result['following']),
        ]);
    }

    /**
     * ユーザープロフィールを更新する。
     *
     * @param  UpdateProfileRequest  $request  バリデーション済みリクエスト
     * @param  User  $user  ルートモデルバインディングで解決したユーザー
     */
    public function update(UpdateProfileRequest $request, User $user): RedirectResponse
    {
        $this->updateUserProfile->execute(
            targetUserId: $user->id,
            authUserId: $request->user()->id,
            name: $request->validated('name'),
            bio: $request->validated('bio'),
            headerImage: $request->file('header_image'),
            profileImage: $request->file('profile_image'),
        );

        return back();
    }
}
