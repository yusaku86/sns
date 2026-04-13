<?php

namespace App\Http\Controllers;

use App\Application\User\GetUserProfileUseCase;
use App\Application\User\UpdateUserProfileUseCase;
use App\Http\Requests\UpdateProfileRequest;
use App\Infrastructure\Eloquent\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        private GetUserProfileUseCase $getUserProfile,
        private UpdateUserProfileUseCase $updateUserProfile,
    ) {}

    public function show(Request $request, User $user): Response
    {
        $result = $this->getUserProfile->execute(
            userId: $user->id,
            authUserId: $request->user()?->id,
        );

        abort_if(! $result, 404);

        return Inertia::render('users/show', [
            'user' => $result['user'],
            'posts' => $result['posts'],
            'replies' => $result['replies'],
            'likedPosts' => $result['likedPosts'],
        ]);
    }

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
