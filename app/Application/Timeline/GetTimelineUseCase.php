<?php

namespace App\Application\Timeline;

use App\Application\Shared\FeedMerger;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Retweet\Repositories\RetweetRepositoryInterface;

/**
 * タイムライン投稿一覧をカーソルページネーション付きで取得するユースケース。
 */
class GetTimelineUseCase
{
    private const LIMIT = 20;

    public function __construct(
        private PostRepositoryInterface $postRepository,
        private RetweetRepositoryInterface $retweetRepository,
        private FeedMerger $feedMerger,
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
        $retweets = $this->retweetRepository->getForTimeline($userId, $userId, self::LIMIT + 1, $cursor);

        return $this->feedMerger->paginate($posts, $retweets, self::LIMIT);
    }
}
