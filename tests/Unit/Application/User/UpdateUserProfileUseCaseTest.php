<?php

use App\Application\User\UpdateUserProfileUseCase;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

use function Pest\Laravel\mock;

uses(TestCase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('名前とbioを更新できる', function () {
    $repository = mock(UserRepositoryInterface::class);
    $repository->shouldReceive('update')
        ->once()
        ->withArgs(fn ($id, $name, $bio, $headerImagePath, $profileImagePath) => $id === 'user-1'
            && $name === '新しい名前'
            && $bio === '新しいbio'
            && $headerImagePath === null
            && $profileImagePath === null
        );

    $useCase = new UpdateUserProfileUseCase($repository);
    $useCase->execute(
        targetUserId: 'user-1',
        authUserId: 'user-1',
        name: '新しい名前',
        bio: '新しいbio',
    );
});

it('ヘッダー画像をアップロードして更新できる', function () {
    $repository = mock(UserRepositoryInterface::class);
    $repository->shouldReceive('update')
        ->once()
        ->withArgs(fn ($id, $name, $bio, $headerImagePath, $profileImagePath) => $id === 'user-1'
            && $headerImagePath !== null
            && str_starts_with($headerImagePath, 'header_images/')
            && $profileImagePath === null
        );

    $file = UploadedFile::fake()->image('header.jpg', 1200, 400);

    $useCase = new UpdateUserProfileUseCase($repository);
    $useCase->execute(
        targetUserId: 'user-1',
        authUserId: 'user-1',
        name: '名前',
        bio: null,
        headerImage: $file,
    );
});

it('プロフィール画像をアップロードして更新できる', function () {
    $repository = mock(UserRepositoryInterface::class);
    $repository->shouldReceive('update')
        ->once()
        ->withArgs(fn ($id, $name, $bio, $headerImagePath, $profileImagePath) => $id === 'user-1'
            && $headerImagePath === null
            && $profileImagePath !== null
            && str_starts_with($profileImagePath, 'profile_images/')
        );

    $file = UploadedFile::fake()->image('avatar.png', 400, 400);

    $useCase = new UpdateUserProfileUseCase($repository);
    $useCase->execute(
        targetUserId: 'user-1',
        authUserId: 'user-1',
        name: '名前',
        bio: null,
        profileImage: $file,
    );
});

it('他のユーザーのプロフィールは更新できない', function () {
    $repository = mock(UserRepositoryInterface::class);
    $repository->shouldNotReceive('update');

    $useCase = new UpdateUserProfileUseCase($repository);

    expect(fn () => $useCase->execute(
        targetUserId: 'user-1',
        authUserId: 'user-2',
        name: '名前',
        bio: null,
    ))->toThrow(AuthorizationException::class);
});
