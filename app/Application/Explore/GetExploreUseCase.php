<?php

namespace App\Application\Explore;

use App\Application\Shared\FeedMerger;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Retweet\Repositories\RetweetRepositoryInterface;

/**
 * 探索ページ用の全投稿一覧をカーソルページネーション付きで取得するユースケース。
 */
class GetExploreUseCase
{
    private const LIMIT = 20;

    public function __construct(
        private PostRepositoryInterface $postRepository,
        private RetweetRepositoryInterface $retweetRepository,
        private FeedMerger $feedMerger,
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
        $retweets = $this->retweetRepository->getAllAsPost($authUserId, self::LIMIT + 1, $cursor);

        return $this->feedMerger->paginate($posts, $retweets, self::LIMIT);
    }
}
