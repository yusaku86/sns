<?php

use App\Http\Controllers\ExploreController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\HashtagController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\RetweetController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// 認証不要
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/explore', [ExploreController::class, 'index'])->name('explore');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::get('/hashtags/{hashtag}', [HashtagController::class, 'show'])->name('hashtags.show');
});

// 認証必要（メール確認済み）
Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('throttle:120,1')->get('/', [TimelineController::class, 'index'])->name('timeline');
    Route::redirect('/dashboard', '/')->name('dashboard');

    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
        Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
        Route::post('/posts/{post}/replies', [ReplyController::class, 'store'])->name('replies.store');

        Route::post('/posts/{post}/like', [LikeController::class, 'store'])->name('likes.store');
        Route::delete('/posts/{post}/like', [LikeController::class, 'destroy'])->name('likes.destroy');

        Route::post('/posts/{post}/retweet', [RetweetController::class, 'store'])->name('retweets.store');
        Route::delete('/posts/{post}/retweet', [RetweetController::class, 'destroy'])->name('retweets.destroy');

        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/follow', [FollowController::class, 'store'])->name('follows.store');
        Route::delete('/users/{user}/follow', [FollowController::class, 'destroy'])->name('follows.destroy');
    });

});

require __DIR__.'/settings.php';
