<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Post\Entities\Post as PostEntity;
use App\Domain\Post\Entities\PostImage as PostImageEntity;
use App\Domain\Retweet\Repositories\RetweetRepositoryInterface;
use App\Infrastructure\Eloquent\Models\Follow;
use App\Infrastructure\Eloquent\Models\Post as PostModel;
use App\Infrastructure\Eloquent\Models\PostImage as PostImageModel;
use App\Infrastructure\Eloquent\Models\Retweet as RetweetModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

/**
 * Eloquentを使ったリツイートリポジトリの実装。
 */
class EloquentRetweetRepository implements RetweetRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function exists(string $userId, string $postId): bool
    {
        return RetweetModel::where('user_id', $userId)
            ->where('post_id', $postId)
            ->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function save(string $userId, string $postId): void
    {
        RetweetModel::create([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $userId, string $postId): void
    {
        RetweetModel::where('user_id', $userId)
            ->where('post_id', $postId)
            ->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function getForTimeline(string $userId, ?string $authUserId, int $limit = 20, ?string $cursor = null): array
    {
        $followingIds = Follow::where('follower_id', $userId)->pluck('following_id');
        $targetIds = $followingIds->concat([$userId])->unique()->values();

        $query = RetweetModel::with([
            'user',
            'post' => fn ($q) => $q->with(['user', 'hashtags', 'images'])->withCount(['likes', 'replies', 'retweets']),
        ])
            ->whereIn('user_id', $targetIds)
            ->latest();

        $this->applyCursor($query, $cursor);

        return $query->limit($limit)->get()
            ->map(fn (RetweetModel $rt) => $this->toEntity($rt, $authUserId))
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAsPost(?string $authUserId = null, int $limit = 20, ?string $cursor = null): array
    {
        $query = RetweetModel::with([
            'user',
            'post' => fn ($q) => $q->with(['user', 'hashtags', 'images'])->withCount(['likes', 'replies', 'retweets']),
        ])->latest();

        $this->applyCursor($query, $cursor);

        return $query->limit($limit)->get()
            ->map(fn (RetweetModel $rt) => $this->toEntity($rt, $authUserId))
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getByUserIdAsPost(string $userId, ?string $authUserId = null, int $limit = 20, ?string $cursor = null): array
    {
        $query = RetweetModel::with([
            'user',
            'post' => fn ($q) => $q->with(['user', 'hashtags', 'images'])->withCount(['likes', 'replies', 'retweets']),
        ])
            ->where('user_id', $userId)
            ->latest();

        $this->applyCursor($query, $cursor);

        return $query->limit($limit)->get()
            ->map(fn (RetweetModel $rt) => $this->toEntity($rt, $authUserId))
            ->all();
    }

    /**
     * カーソルが有効な場合、created_at による絞り込みをクエリに適用する。
     * 無効なカーソルは無視してカーソルなしと同等に扱う。
     *
     * @param  Builder<RetweetModel>  $query
     */
    private function applyCursor(Builder $query, ?string $cursor): void
    {
        if ($cursor === null) {
            return;
        }

        try {
            $formatted = (new \DateTimeImmutable($cursor))->format('Y-m-d H:i:s');
            $query->where('created_at', '<', $formatted);
        } catch (\Exception) {
            // 無効なカーソルは無視してカーソルなしと同等に扱う
        }
    }

    /**
     * RetweetモデルからPostエンティティを生成する。
     *
     * @param  RetweetModel  $retweet  リツイートモデル
     * @param  string|null  $authUserId  認証ユーザーID
     */
    private function toEntity(RetweetModel $retweet, ?string $authUserId): PostEntity
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
            images: $this->toImageEntities($model),
        );
    }

    /**
     * PostモデルのimagesリレーションからPostImageエンティティ配列を生成する。
     *
     * @param  PostModel  $model  投稿モデル
     * @return PostImageEntity[]
     */
    private function toImageEntities(PostModel $model): array
    {
        return $model->images
            ->map(fn (PostImageModel $img) => new PostImageEntity(
                id: $img->id,
                postId: $img->post_id,
                path: $img->path,
                order: $img->order,
            ))
            ->all();
    }
}
