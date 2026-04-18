<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Reply\Entities\Reply as ReplyEntity;
use App\Domain\Reply\Repositories\ReplyRepositoryInterface;
use App\Infrastructure\Eloquent\Models\Reply as ReplyModel;
use Illuminate\Support\Facades\Storage;

/**
 * Eloquentを使ったリプライリポジトリの実装。
 */
class EloquentReplyRepository implements ReplyRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getByPostId(string $postId): array
    {
        return ReplyModel::with('user')
            ->where('post_id', $postId)
            ->oldest()
            ->get()
            ->map(fn ($model) => $this->toEntity($model))
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getByUserId(string $userId, int $limit = 20): array
    {
        return ReplyModel::with(['user', 'post.user'])
            ->where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($model) => $this->toEntityWithPost($model))
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function save(ReplyEntity $reply): void
    {
        ReplyModel::create([
            'id' => $reply->id,
            'post_id' => $reply->postId,
            'user_id' => $reply->userId,
            'content' => $reply->content,
        ]);
    }

    /**
     * ReplyモデルからReplyエンティティを生成する（元投稿情報なし）。
     *
     * @param  ReplyModel  $model  リプライモデル
     */
    private function toEntity(ReplyModel $model): ReplyEntity
    {
        return new ReplyEntity(
            id: $model->id,
            postId: $model->post_id,
            userId: $model->user_id,
            userName: $model->user->name,
            userHandle: $model->user->handle,
            content: $model->content,
            createdAt: new \DateTimeImmutable($model->created_at),
            userProfileImageUrl: $model->user->profile_image
                ? Storage::disk('public')->url($model->user->profile_image)
                : null,
        );
    }

    /**
     * ReplyモデルからReplyエンティティを生成する（元投稿情報付き）。
     *
     * @param  ReplyModel  $model  リプライモデル
     */
    private function toEntityWithPost(ReplyModel $model): ReplyEntity
    {
        return new ReplyEntity(
            id: $model->id,
            postId: $model->post_id,
            userId: $model->user_id,
            userName: $model->user->name,
            userHandle: $model->user->handle,
            content: $model->content,
            createdAt: new \DateTimeImmutable($model->created_at),
            postContent: $model->post->content,
            postUserName: $model->post->user->name,
            postUserHandle: $model->post->user->handle,
            userProfileImageUrl: $model->user->profile_image
                ? Storage::disk('public')->url($model->user->profile_image)
                : null,
        );
    }
}
