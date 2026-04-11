<?php

use App\Infrastructure\Eloquent\Models\Follow;
use App\Infrastructure\Eloquent\Models\User;

it('ユーザーをフォローできる', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($user)
        ->post(route('follows.store', $target))
        ->assertRedirect();

    expect(Follow::where('follower_id', $user->id)->where('following_id', $target->id)->exists())->toBeTrue();
});

it('フォローを解除できる', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    Follow::create(['follower_id' => $user->id, 'following_id' => $target->id]);

    $this->actingAs($user)
        ->delete(route('follows.destroy', $target))
        ->assertRedirect();

    expect(Follow::where('follower_id', $user->id)->where('following_id', $target->id)->exists())->toBeFalse();
});

it('未ログインユーザーはフォローできない', function () {
    $target = User::factory()->create();

    $this->post(route('follows.store', $target))
        ->assertRedirect(route('login'));
});
