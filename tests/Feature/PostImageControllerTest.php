<?php

use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\PostImage;
use App\Infrastructure\Eloquent\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('ログイン済みユーザーは画像付きで投稿できる', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('posts.store'), [
            'content' => '画像付き投稿',
            'images' => [
                UploadedFile::fake()->image('photo1.jpg'),
                UploadedFile::fake()->image('photo2.png'),
            ],
        ])
        ->assertRedirect();

    $post = Post::where('user_id', $user->id)->first();
    expect($post)->not->toBeNull();
    expect(PostImage::where('post_id', $post->id)->count())->toBe(2);
});

it('テキストなし画像のみで投稿できる', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('posts.store'), [
            'content' => null,
            'images' => [
                UploadedFile::fake()->image('photo.jpg'),
            ],
        ])
        ->assertRedirect();

    $post = Post::where('user_id', $user->id)->first();
    expect($post)->not->toBeNull();
    expect(PostImage::where('post_id', $post->id)->count())->toBe(1);
});

it('テキストも画像もない場合は投稿できない', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('posts.store'), [])
        ->assertSessionHasErrors('content');
});

it('9枚以上の画像は投稿できない', function () {
    $user = User::factory()->create();

    $images = array_fill(0, 9, UploadedFile::fake()->image('photo.jpg'));

    $this->actingAs($user)
        ->post(route('posts.store'), [
            'content' => 'テスト',
            'images' => $images,
        ])
        ->assertSessionHasErrors('images');
});

it('SVGファイルはアップロードできない', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('posts.store'), [
            'content' => 'テスト',
            'images' => [
                UploadedFile::fake()->create('evil.svg', 10, 'image/svg+xml'),
            ],
        ])
        ->assertSessionHasErrors('images.0');
});

it('10MBを超えるファイルはアップロードできない', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('posts.store'), [
            'content' => 'テスト',
            'images' => [
                UploadedFile::fake()->image('large.jpg')->size(10241),
            ],
        ])
        ->assertSessionHasErrors('images.0');
});

it('投稿削除時に画像ファイルも削除される', function () {
    $user = User::factory()->create();

    // 投稿と画像を作成
    $this->actingAs($user)
        ->post(route('posts.store'), [
            'content' => '削除テスト',
            'images' => [UploadedFile::fake()->image('delete-me.jpg')],
        ])
        ->assertRedirect();

    $post = Post::where('user_id', $user->id)->first();
    $imagePath = PostImage::where('post_id', $post->id)->first()->path;

    Storage::disk('local')->assertExists($imagePath);

    $this->actingAs($user)
        ->delete(route('posts.destroy', $post))
        ->assertRedirect();

    Storage::disk('local')->assertMissing($imagePath);
});
