<?php

namespace App\Infrastructure\Eloquent\Repositories;

use App\Domain\Reply\Entities\Reply as ReplyEntity;
use App\Domain\Reply\Repositories\ReplyRepositoryInterface;
use App\Infrastructure\Eloquent\Models\Reply as ReplyModel;

class EloquentReplyRepository implements ReplyRepositoryInterface
{
    public function getByPostId(string $postId): array
    {
        return ReplyModel::with('user')
            ->where('post_id', $postId)
            ->oldest()
            ->get()
            ->map(fn ($model) => $this->toEntity($model))
            ->all();
    }

    public function save(ReplyEntity $reply): void
    {
        ReplyModel::create([
            'id' => $reply->id,
            'post_id' => $reply->postId,
            'user_id' => $reply->userId,
            'content' => $reply->content,
        ]);
    }

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
        );
    }
}
