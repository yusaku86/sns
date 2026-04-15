<?php

namespace App\Http\Middleware;

use App\Application\Hashtag\GetTrendingHashtagsUseCase;
use App\Infrastructure\Eloquent\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(
        private GetTrendingHashtagsUseCase $getTrendingHashtags,
    ) {}

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? $this->serializeUser($user) : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'currentTeam' => fn () => $user?->currentTeam ? $user->toUserTeam($user->currentTeam) : null,
            'teams' => fn () => $user?->toUserTeams(includeCurrent: true) ?? [],
            'trendingHashtags' => fn () => $this->getTrendingHashtags->execute(),
        ];
    }

    /**
     * フロントエンドに必要なフィールドのみを渡す。
     * Eloquent モデルを直接渡すと不要なフィールドが露出するリスクがあるため
     * 明示的に許可したフィールドのみシリアライズする。
     *
     * @return array<string, mixed>
     */
    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'profile_image_url' => $user->profile_image_url,
            'two_factor_enabled' => ! is_null($user->two_factor_confirmed_at),
        ];
    }
}
