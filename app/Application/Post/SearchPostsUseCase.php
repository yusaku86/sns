<?php

namespace App\Application\Post;

use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;

/**
 * 投稿本文をキーワードで部分一致検索するユースケース。
 */
class SearchPostsUseCase
{
    private const LIMIT = 20;

    public function __construct(
        private PostRepositoryInterface $postRepository,
    ) {}

    /**
     * キーワードで投稿を検索してカーソルページネーション付きで返す。
     *
     * @param  string  $keyword  検索キーワード
     * @param  string|null  $authUserId  認証ユーザーID
     * @param  string|null  $cursor  ページネーションカーソル
     * @return array{posts: Post[], nextCursor: string|null, hasMore: bool}
     */
    public function execute(string $keyword, ?string $authUserId = null, ?string $cursor = null): array
    {
        if (trim($keyword) === '') {
            return ['posts' => [], 'nextCursor' => null, 'hasMore' => false];
        }

        $posts = $this->postRepository->searchByKeyword($keyword, $authUserId, self::LIMIT + 1, $cursor);

        $hasMore = count($posts) > self::LIMIT;

        if ($hasMore) {
            array_pop($posts);
        }

        $lastPost = end($posts);
        $nextCursor = ($hasMore && $lastPost)
            ? $lastPost->createdAt->format(\DateTimeInterface::ATOM)
            : null;

        return [
            'posts' => $posts,
            'nextCursor' => $nextCursor,
            'hasMore' => $hasMore,
        ];
    }
}
