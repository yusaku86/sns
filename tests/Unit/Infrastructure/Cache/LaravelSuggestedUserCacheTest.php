<?php

use App\Domain\Follow\Entities\FollowUser;
use App\Infrastructure\Cache\LaravelSuggestedUserCache;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;

function makeCache(): LaravelSuggestedUserCache
{
    return new LaravelSuggestedUserCache(new Repository(new ArrayStore));
}

it('put して get すると FollowUser インスタンスの配列が返る', function () {
    $cache = makeCache();
    $users = [
        new FollowUser('uuid-1', '田中', 'tanaka', null, false),
        new FollowUser('uuid-2', '鈴木', 'suzuki', 'https://example.com/img.jpg', true),
    ];

    $cache->put('auth-user', $users);
    $result = $cache->get('auth-user');

    expect($result)->toHaveCount(2)
        ->and($result[0])->toBeInstanceOf(FollowUser::class)
        ->and($result[0]->id)->toBe('uuid-1')
        ->and($result[0]->name)->toBe('田中')
        ->and($result[0]->isFollowedByAuthUser)->toBeFalse()
        ->and($result[1]->isFollowedByAuthUser)->toBeTrue();
});

it('キャッシュがない場合 get は null を返す', function () {
    $cache = makeCache();

    expect($cache->get('no-such-user'))->toBeNull();
});

it('put 直後は isFresh が true を返す', function () {
    $cache = makeCache();
    $cache->put('auth-user', []);

    expect($cache->isFresh('auth-user'))->toBeTrue();
});

it('キャッシュがない場合 isFresh は false を返す', function () {
    $cache = makeCache();

    expect($cache->isFresh('no-such-user'))->toBeFalse();
});
