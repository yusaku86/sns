<?php

namespace App\Providers;

use App\Domain\Follow\Repositories\FollowRepositoryInterface;
use App\Domain\Hashtag\Repositories\HashtagRepositoryInterface;
use App\Domain\Like\Repositories\LikeRepositoryInterface;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Reply\Repositories\ReplyRepositoryInterface;
use App\Domain\Retweet\Repositories\RetweetRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Eloquent\Models\Post as PostModel;
use App\Infrastructure\Eloquent\Observers\PostObserver;
use App\Infrastructure\Eloquent\Repositories\EloquentFollowRepository;
use App\Infrastructure\Eloquent\Repositories\EloquentHashtagRepository;
use App\Infrastructure\Eloquent\Repositories\EloquentLikeRepository;
use App\Infrastructure\Eloquent\Repositories\EloquentPostRepository;
use App\Infrastructure\Eloquent\Repositories\EloquentReplyRepository;
use App\Infrastructure\Eloquent\Repositories\EloquentRetweetRepository;
use App\Infrastructure\Eloquent\Repositories\EloquentUserRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(PostRepositoryInterface::class, EloquentPostRepository::class);
        $this->app->bind(LikeRepositoryInterface::class, EloquentLikeRepository::class);
        $this->app->bind(FollowRepositoryInterface::class, EloquentFollowRepository::class);
        $this->app->bind(ReplyRepositoryInterface::class, EloquentReplyRepository::class);
        $this->app->bind(RetweetRepositoryInterface::class, EloquentRetweetRepository::class);
        $this->app->bind(HashtagRepositoryInterface::class, EloquentHashtagRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        PostModel::observe(PostObserver::class);
        $this->configureDefaults();
        $this->configureFactories();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureFactories(): void
    {
        // Infrastructure層のモデルに対するFactory名を正しく解決する
        // App\Infrastructure\Eloquent\Models\User → Database\Factories\UserFactory
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            $infraPrefix = 'App\\Infrastructure\\Eloquent\\Models\\';

            $shortName = str_starts_with($modelName, $infraPrefix)
                ? substr($modelName, strlen($infraPrefix))
                : class_basename($modelName);

            return 'Database\\Factories\\'.$shortName.'Factory';
        });
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
