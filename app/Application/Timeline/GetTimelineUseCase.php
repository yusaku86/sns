<?php

namespace App\Application\Timeline;

use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;

/**
 * タイムライン投稿一覧をカーソルページネーション付きで取得するユースケース。
 */
class GetTimelineUseCase
{
    private const LIMIT = 20;

    public function __construct(
        private PostRepositoryInterface $postRepository,
    ) {}

    /**
     * タイムライン投稿一覧を返す。
     *
     * @param  string  $userId  対象ユーザーID
     * @param  string|null  $cursor  ページネーションカーソル（前回レスポンスの nextCursor）
     * @return array{posts: Post[], nextCursor: string|null, hasMore: bool}
     */
    public function execute(string $userId, ?string $cursor = null): array
    {
        $posts = $this->postRepository->getTimeline($userId, self::LIMIT + 1, $cursor);

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
