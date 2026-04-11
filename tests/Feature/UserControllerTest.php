<?php

use App\Infrastructure\Eloquent\Models\User;

it('プロフィールページを表示できる', function () {
    $user = User::factory()->create();

    $this->get(route('users.show', $user))
        ->assertOk();
});

it('自分のプロフィールを更新できる', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('users.update', $user), [
            'name' => '更新後の名前',
            'bio' => '更新後のbio',
        ])
        ->assertRedirect();

    expect($user->fresh()->name)->toBe('更新後の名前')
        ->and($user->fresh()->bio)->toBe('更新後のbio');
});

it('他のユーザーのプロフィールは更新できない', function () {
    $user = User::factory()->create();
    $other = User::factory()->create(['name' => '変更前']);

    $this->actingAs($user)
        ->put(route('users.update', $other), [
            'name' => '変更後',
            'bio' => null,
        ])
        ->assertForbidden();

    expect($other->fresh()->name)->toBe('変更前');
});
