<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Post\Entities\Post as PostEntity;
use App\Domain\Post\Entities\PostImage as PostImageEntity;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Infrastructure\Eloquent\Models\Follow;
use App\Infrastructure\Eloquent\Models\Hashtag as HashtagModel;
use App\Infrastructure\Eloquent\Models\Post as PostModel;
use App\Infrastructure\Eloquent\Models\PostImage as PostImageModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Eloquentを使った投稿リポジトリの実装。投稿のみを返し、リツイートとのマージはApplication層が担う。
 */
class EloquentPostRepository implements PostRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findById(string $id, ?string $authUserId = null): ?PostEntity
    {
        $model = PostModel::with(['user', 'hashtags', 'images'])->withCount(['likes', 'replies', 'retweets'])->find($id);

        if (! $model) {
            return null;
        }

        return $this->toEntity($model, $authUserId);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeline(string $userId, int $limit = 20, ?string $cursor = null): array
    {
        $followingIds = Follow::where('follower_id', $userId)->pluck('following_id');
        $targetIds = $followingIds->concat([$userId])->unique()->values();

        $query = PostModel::with(['user', 'hashtags', 'images'])
            ->withCount(['likes', 'replies', 'retweets'])
            ->whereIn('user_id', $targetIds)
            ->latest();

        if ($cursor !== null) {
            try {
                $formatted = (new \DateTimeImmutable($cursor))->format('Y-m-d H:i:s');
                $query->where('created_at', '<', $formatted);
            } catch (\Exception) {
                // 無効なカーソルは無視してカーソルなしと同等に扱う
            }
        }

        return $query->limit($limit)->get()
            ->map(fn (PostModel $model) => $this->toEntity($model, $userId))
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(?string $authUserId = null, int $limit = 20, ?string $cursor = null): array
    {
        $query = PostModel::with(['user', 'hashtags', 'images'])
            ->withCount(['likes', 'replies', 'retweets'])
            ->latest();

        if ($cursor !== null) {
            try {
                $formatted = (new \DateTimeImmutable($cursor))->format('Y-m-d H:i:s');
                $query->where('created_at', '<', $formatted);
            } catch (\Exception) {
                // 無効なカーソルは無視してカーソルなしと同等に扱う
            }
        }

        return $query->limit($limit)->get()
            ->map(fn (PostModel $model) => $this->toEntity($model, $authUserId))
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function save(PostEntity $post): void
    {
        PostModel::create([
            'id' => $post->id,
            'user_id' => $post->userId,
            'content' => $post->content,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $id): void
    {
        PostModel::destroy($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getByUserId(string $userId, ?string $authUserId = null, int $limit = 20, ?string $cursor = null): array
    {
        $query = PostModel::with(['user', 'hashtags', 'images'])
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

    /**
     * {@inheritdoc}
     */
    public function getByHashtag(string $hashtagName, ?string $authUserId = null, int $limit = 20, ?string $cursor = null): array
    {
        $hashtag = HashtagModel::where('name', $hashtagName)->first();

        if (! $hashtag) {
            return [];
        }

        $query = $hashtag->posts()
            ->with(['user', 'hashtags', 'images'])
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

    /**
     * {@inheritdoc}
     */
    public function searchByKeyword(string $keyword, ?string $authUserId = null, int $limit = 20, ?string $cursor = null): array
    {
        // 検索は投稿本文のみ対象。リツイートは除外する。
        $escaped = addcslashes($keyword, '%_\\');

        $query = PostModel::with(['user', 'hashtags', 'images'])
            ->withCount(['likes', 'replies', 'retweets'])
            ->where('content', 'like', '%'.$escaped.'%')
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

    /**
     * PostモデルからPostエンティティを生成する。
     *
     * @param  PostModel  $model  投稿モデル
     * @param  string|null  $authUserId  認証ユーザーID
     */
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
