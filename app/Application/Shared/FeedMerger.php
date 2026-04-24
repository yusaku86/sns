<?php

namespace App\Application\Shared;

use App\Domain\Post\Entities\Post;

/**
 * 投稿とリツイートをマージし、カーソルページネーション付きフィードを構築するサービス。
 */
class FeedMerger
{
    /**
     * 投稿とリツイートを日時降順でマージし、ページネーション結果を返す。
     * 各リポジトリから $limit 件ずつ取得した配列を渡す。
     *
     * @param  Post[]  $posts  投稿一覧（最大 $limit 件）
     * @param  Post[]  $retweets  リツイート一覧（最大 $limit 件）
     * @param  int  $limit  ページあたりの最大件数
     * @return array{posts: Post[], nextCursor: string|null, hasMore: bool}
     */
    public function paginate(array $posts, array $retweets, int $limit): array
    {
        $merged = collect($posts)->merge($retweets)
            ->sortByDesc(fn (Post $p) => ($p->retweetedAt ?? $p->createdAt)->getTimestamp())
            ->take($limit + 1)
            ->values()
            ->all();

        $hasMore = count($merged) > $limit;

        if ($hasMore) {
            array_pop($merged);
        }

        $lastPost = end($merged);
        $nextCursor = ($hasMore && $lastPost)
            ? ($lastPost->retweetedAt ?? $lastPost->createdAt)->format(\DateTimeInterface::ATOM)
            : null;

        return [
            'posts' => $merged,
            'nextCursor' => $nextCursor,
            'hasMore' => $hasMore,
        ];
    }
}
