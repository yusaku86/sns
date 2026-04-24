<?php

namespace App\Application\User;

use App\Application\Shared\FeedMerger;
use App\Domain\Follow\Repositories\FollowRepositoryInterface;
use App\Domain\Like\Repositories\LikeRepositoryInterface;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Reply\Repositories\ReplyRepositoryInterface;
use App\Domain\Retweet\Repositories\RetweetRepositoryInterface;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;

/**
 * ユーザープロフィールと関連データ（投稿・リプライ・いいね・フォロー）を取得するユースケース。
 */
class GetUserProfileUseCase
{
    private const LIMIT = 20;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PostRepositoryInterface $postRepository,
        private RetweetRepositoryInterface $retweetRepository,
        private ReplyRepositoryInterface $replyRepository,
        private LikeRepositoryInterface $likeRepository,
        private FollowRepositoryInterface $followRepository,
        private FeedMerger $feedMerger,
    ) {}

    /**
     * ユーザープロフィールと関連データを返す。ユーザーが見つからない場合はnullを返す。
     *
     * @param  string  $userId  対象ユーザーID
     * @param  string|null  $authUserId  認証ユーザーID（フォロー・いいね状態の付与に使用）
     * @param  string|null  $cursor  投稿一覧のページネーションカーソル
     * @return array{user: User, posts: Post[], nextCursor: string|null, hasMore: bool, replies: array, likedPosts: array, followers: array, following: array}|null
     */
    public function execute(string $userId, ?string $authUserId = null, ?string $cursor = null): ?array
    {
        $user = $this->userRepository->findById($userId, $authUserId);

        if (! $user) {
            return null;
        }

        $posts = $this->postRepository->getByUserId($userId, $authUserId, self::LIMIT + 1, $cursor);
        $retweets = $this->retweetRepository->getByUserIdAsPost($userId, $authUserId, self::LIMIT + 1, $cursor);
        $paginated = $this->feedMerger->paginate($posts, $retweets, self::LIMIT);

        return [
            'user' => $user,
            'posts' => $paginated['posts'],
            'nextCursor' => $paginated['nextCursor'],
            'hasMore' => $paginated['hasMore'],
            'replies' => $this->replyRepository->getByUserId($userId),
            'likedPosts' => $this->likeRepository->getLikedPostsByUserId($userId, $authUserId),
            'followers' => $this->followRepository->getFollowers($userId, $authUserId),
            'following' => $this->followRepository->getFollowing($userId, $authUserId),
        ];
    }
}
