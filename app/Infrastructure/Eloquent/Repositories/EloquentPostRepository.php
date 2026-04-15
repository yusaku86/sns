<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Post\Entities\Post as PostEntity;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Infrastructure\Eloquent\Models\Follow;
use App\Infrastructure\Eloquent\Models\Hashtag as HashtagModel;
use App\Infrastructure\Eloquent\Models\Post as PostModel;
use App\Infrastructure\Eloquent\Models\Retweet as RetweetModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

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

    public function getTimeline(string $userId, int $limit = 20, ?string $cursor = null): array
    {
        $followingIds = Follow::where('follower_id', $userId)
            ->pluck('following_id');

        $postQuery = PostModel::with(['user', 'hashtags'])
            ->withCount(['likes', 'replies', 'retweets'])
            ->whereIn('user_id', $followingIds)
            ->latest();

        $retweetQuery = RetweetModel::with([
            'user',
            'post' => fn ($q) => $q->with(['user', 'hashtags'])->withCount(['likes', 'replies', 'retweets']),
        ])
            ->whereIn('user_id', $followingIds)
            ->latest();

        if ($cursor !== null) {
            try {
                $cursorTime = new \DateTimeImmutable($cursor);
                $formatted = $cursorTime->format('Y-m-d H:i:s');
                $postQuery->where('created_at', '<', $formatted);
                $retweetQuery->where('created_at', '<', $formatted);
            } catch (\Exception) {
                // 無効なカーソルは無視してカーソルなしと同等に扱う
            }
        }

        $posts = $postQuery->limit($limit)->get()
            ->map(fn ($model) => $this->toEntity($model, $userId));

        $retweets = $retweetQuery->limit($limit)->get()
            ->map(fn ($rt) => $this->toEntityFromRetweet($rt, $userId));

        return collect($posts)->merge($retweets)
            ->sortByDesc(fn (PostEntity $p) => ($p->retweetedAt ?? $p->createdAt)->getTimestamp())
            ->take($limit)
            ->values()
            ->all();
    }

    public function getAll(?string $authUserId = null, int $limit = 20, ?string $cursor = null): array
    {
        $postQuery = PostModel::with(['user', 'hashtags'])
            ->withCount(['likes', 'replies', 'retweets'])
            ->latest();

        $retweetQuery = RetweetModel::with([
            'user',
            'post' => fn ($q) => $q->with(['user', 'hashtags'])->withCount(['likes', 'replies', 'retweets']),
        ])
            ->latest();

        if ($cursor !== null) {
            try {
                $cursorTime = new \DateTimeImmutable($cursor);
                $formatted = $cursorTime->format('Y-m-d H:i:s');
                $postQuery->where('created_at', '<', $formatted);
                $retweetQuery->where('created_at', '<', $formatted);
            } catch (\Exception) {
                // 無効なカーソルは無視してカーソルなしと同等に扱う
            }
        }

        $posts = $postQuery->limit($limit)->get()
            ->map(fn ($model) => $this->toEntity($model, $authUserId));

        $retweets = $retweetQuery->limit($limit)->get()
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

    public function getByUserId(string $userId, ?string $authUserId = null, int $limit = 20, ?string $cursor = null): array
    {
        $query = PostModel::with(['user', 'hashtags'])
            ->withCount(['likes', 'replies', 'retweets'])
            ->where('user_id', $userId)
            ->latest();

        if ($cursor !== null) {
            try {
                $cursorTime = new \DateTimeImmutable($cursor);
                $query->where('created_at', '<', $cursorTime->format('Y-m-d H:i:s'));
            } catch (\Exception) {
                // 無効なカーソルは無視してカーソルなしと同等に扱う
            }
        }

        return $query->limit($limit)->get()
            ->map(fn (PostModel $model) => $this->toEntity($model, $authUserId))
            ->all();
    }

    public function getByHashtag(string $hashtagName, ?string $authUserId = null, int $limit = 20, ?string $cursor = null): array
    {
        $hashtag = HashtagModel::where('name', $hashtagName)->first();

        if (! $hashtag) {
            return [];
        }

        $query = $hashtag->posts()
            ->with(['user', 'hashtags'])
            ->withCount(['likes', 'replies', 'retweets'])
            ->latest();

        if ($cursor !== null) {
            try {
                $cursorTime = new \DateTimeImmutable($cursor);
                $query->where('posts.created_at', '<', $cursorTime->format('Y-m-d H:i:s'));
            } catch (\Exception) {
                // 無効なカーソルは無視してカーソルなしと同等に扱う
            }
        }

        /** @var Collection<int, PostModel> $results */
        $results = $query->limit($limit)->get();

        return $results
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
            userProfileImageUrl: $model->user->profile_image
                ? Storage::disk('public')->url($model->user->profile_image)
                : null,
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
            userProfileImageUrl: $model->user->profile_image
                ? Storage::disk('public')->url($model->user->profile_image)
                : null,
        );
    }
}
