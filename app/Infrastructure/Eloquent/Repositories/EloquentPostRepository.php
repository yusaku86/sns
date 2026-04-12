<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Post\Entities\Post as PostEntity;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Infrastructure\Eloquent\Models\Follow;
use App\Infrastructure\Eloquent\Models\Hashtag as HashtagModel;
use App\Infrastructure\Eloquent\Models\Post as PostModel;
use App\Infrastructure\Eloquent\Models\Retweet as RetweetModel;
use Illuminate\Support\Collection;

class EloquentPostRepository implements PostRepositoryInterface
{
    public function findById(string $id, ?string $authUserId = null): ?PostEntity
    {
        $model = PostModel::with(['user', 'hashtags'])->withCount(['likes', 'replies', 'retweets'])->find($id);

        if (! $model) {
            return null;
        }

        return $this->toEntity($model, $authUserId);
    }

    public function getTimeline(string $userId, int $limit = 20): array
    {
        $followingIds = Follow::where('follower_id', $userId)
            ->pluck('following_id');

        $posts = PostModel::with(['user', 'hashtags'])
            ->withCount(['likes', 'replies', 'retweets'])
            ->whereIn('user_id', $followingIds)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($model) => $this->toEntity($model, $userId));

        $retweets = RetweetModel::with([
            'user',
            'post' => fn ($q) => $q->with(['user', 'hashtags'])->withCount(['likes', 'replies', 'retweets']),
        ])
            ->whereIn('user_id', $followingIds)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($rt) => $this->toEntityFromRetweet($rt, $userId));

        return collect($posts)->merge($retweets)
            ->sortByDesc(fn (PostEntity $p) => ($p->retweetedAt ?? $p->createdAt)->getTimestamp())
            ->take($limit)
            ->values()
            ->all();
    }

    public function getAll(?string $authUserId = null, int $limit = 20): array
    {
        $posts = PostModel::with(['user', 'hashtags'])
            ->withCount(['likes', 'replies', 'retweets'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($model) => $this->toEntity($model, $authUserId));

        $retweets = RetweetModel::with([
            'user',
            'post' => fn ($q) => $q->with(['user', 'hashtags'])->withCount(['likes', 'replies', 'retweets']),
        ])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($rt) => $this->toEntityFromRetweet($rt, $authUserId));

        return collect($posts)->merge($retweets)
            ->sortByDesc(fn (PostEntity $p) => ($p->retweetedAt ?? $p->createdAt)->getTimestamp())
            ->take($limit)
            ->values()
            ->all();
    }

    public function save(PostEntity $post): void
    {
        PostModel::create([
            'id' => $post->id,
            'user_id' => $post->userId,
            'content' => $post->content,
        ]);
    }

    public function delete(string $id): void
    {
        PostModel::destroy($id);
    }

    public function getByHashtag(string $hashtagName, ?string $authUserId = null, int $limit = 20): array
    {
        $hashtag = HashtagModel::where('name', $hashtagName)->first();

        if (! $hashtag) {
            return [];
        }

        /** @var Collection<int, PostModel> $posts */
        $posts = $hashtag->posts()
            ->with(['user', 'hashtags'])
            ->withCount(['likes', 'replies', 'retweets'])
            ->latest()
            ->limit($limit)
            ->get();

        return $posts
            ->map(fn (PostModel $model) => $this->toEntity($model, $authUserId))
            ->all();
    }

    private function toEntityFromRetweet(RetweetModel $retweet, ?string $authUserId): PostEntity
    {
        $model = $retweet->post;

        $likedByAuthUser = $authUserId
            ? $model->likes()->where('user_id', $authUserId)->exists()
            : false;

        $retweetedByAuthUser = $authUserId
            ? $model->retweets()->where('user_id', $authUserId)->exists()
            : false;

        return new PostEntity(
            id: $model->id,
            userId: $model->user_id,
            userName: $model->user->name,
            userHandle: $model->user->handle,
            content: $model->content,
            createdAt: new \DateTimeImmutable($model->created_at),
            likesCount: $model->likes_count,
            likedByAuthUser: $likedByAuthUser,
            repliesCount: $model->replies_count,
            retweetsCount: $model->retweets_count,
            retweetedByAuthUser: $retweetedByAuthUser,
            retweetId: $retweet->id,
            retweetedByUserName: $retweet->user->name,
            retweetedByUserHandle: $retweet->user->handle,
            retweetedAt: new \DateTimeImmutable($retweet->created_at),
            hashtags: $model->hashtags->pluck('name')->all(),
        );
    }

    private function toEntity(PostModel $model, ?string $authUserId): PostEntity
    {
        $likedByAuthUser = $authUserId
            ? $model->likes()->where('user_id', $authUserId)->exists()
            : false;

        $retweetedByAuthUser = $authUserId
            ? $model->retweets()->where('user_id', $authUserId)->exists()
            : false;

        return new PostEntity(
            id: $model->id,
            userId: $model->user_id,
            userName: $model->user->name,
            userHandle: $model->user->handle,
            content: $model->content,
            createdAt: new \DateTimeImmutable($model->created_at),
            likesCount: $model->likes_count,
            likedByAuthUser: $likedByAuthUser,
            repliesCount: $model->replies_count,
            retweetsCount: $model->retweets_count,
            retweetedByAuthUser: $retweetedByAuthUser,
            hashtags: $model->hashtags->pluck('name')->all(),
        );
    }
}
