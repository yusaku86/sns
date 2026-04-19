<?php

use App\Infrastructure\Eloquent\Models\Post;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

test('未確認ユーザーはタイムラインにアクセスするとメール確認画面にリダイレクトされる', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('timeline'))
        ->assertRedirect(route('verification.notice'));
});

test('確認済みユーザーはタイムラインにアクセスできる', function () {
    $user = User::factory()->create();

    $this->withoutVite()
        ->actingAs($user)
        ->get(route('timeline'))
        ->assertOk();
});

test('未確認ユーザーは投稿できない', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post(route('posts.store'), ['body' => 'test'])
        ->assertRedirect(route('verification.notice'));
});

test('未確認ユーザーはいいねできない', function () {
    $post = Post::factory()->create();
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post(route('likes.store', $post))
        ->assertRedirect(route('verification.notice'));
});

test('未確認ユーザーはフォローできない', function () {
    $target = User::factory()->create();
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post(route('follows.store', $target))
        ->assertRedirect(route('verification.notice'));
});

test('登録後に確認メールが送信される', function () {
    Notification::fake();

    $this->post(route('register'), [
        'name' => 'Test User',
        'handle' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->first();

    Notification::assertSentTo(
        $user,
        VerifyEmail::class
    );
});
