<?php

namespace App\Application\Explore;

use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;

/**
 * 探索ページ用の全投稿一覧をカーソルページネーション付きで取得するユースケース。
 */
class GetExploreUseCase
{
    private const LIMIT = 20;

    public function __construct(
        private PostRepositoryInterface $postRepository,
    ) {}

    /**
     * 全投稿一覧を返す。
     *
     * @param  string|null  $authUserId  認証ユーザーID（いいね・リツイート状態の付与に使用）
     * @param  string|null  $cursor  ページネーションカーソル
     * @return array{posts: Post[], nextCursor: string|null, hasMore: bool}
     */
    public function execute(?string $authUserId = null, ?string $cursor = null): array
    {
        $posts = $this->postRepository->getAll($authUserId, self::LIMIT + 1, $cursor);

        return $this->paginate($posts);
    }

    /**
     * カーソルページネーション用の結果配列を組み立てる。
     *
     * @param  Post[]  $posts  LIMIT+1件取得した投稿配列
     * @return array{posts: Post[], nextCursor: string|null, hasMore: bool}
     */
    private function paginate(array $posts): array
    {
        $hasMore = count($posts) > self::LIMIT;

        if ($hasMore) {
            array_pop($posts);
        }

        $lastPost = end($posts);
        $nextCursor = ($hasMore && $lastPost)
            ? ($lastPost->retweetedAt ?? $lastPost->createdAt)->format(\DateTimeInterface::ATOM)
            : null;

        return [
            'posts' => $posts,
            'nextCursor' => $nextCursor,
            'hasMore' => $hasMore,
        ];
    }
}
