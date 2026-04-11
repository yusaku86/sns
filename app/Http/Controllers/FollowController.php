<?php

namespace App\Http\Controllers;

use App\Application\Follow\FollowUserUseCase;
use App\Application\Follow\UnfollowUserUseCase;
use App\Infrastructure\Eloquent\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct(
        private FollowUserUseCase $followUser,
        private UnfollowUserUseCase $unfollowUser,
    ) {}

    public function store(Request $request, User $user): RedirectResponse
    {
        $this->followUser->execute($request->user()->id, $user->id);

        return back();
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->unfollowUser->execute($request->user()->id, $user->id);

        return back();
    }
}
