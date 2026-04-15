<?php

namespace App\Application\User;

use App\Domain\Follow\Repositories\FollowRepositoryInterface;
use App\Domain\Like\Repositories\LikeRepositoryInterface;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Reply\Repositories\ReplyRepositoryInterface;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;

class GetUserProfileUseCase
{
    private const LIMIT = 20;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PostRepositoryInterface $postRepository,
        private ReplyRepositoryInterface $replyRepository,
        private LikeRepositoryInterface $likeRepository,
        private FollowRepositoryInterface $followRepository,
    ) {}

    /**
     * @return array{user: User, posts: Post[], nextCursor: string|null, hasMore: bool, replies: array, likedPosts: array, followers: array, following: array}|null
     */
    public function execute(string $userId, ?string $authUserId = null, ?string $cursor = null): ?array
    {
        $user = $this->userRepository->findById($userId, $authUserId);

        if (! $user) {
            return null;
        }

        $rawPosts = $this->postRepository->getByUserId($userId, $authUserId, self::LIMIT + 1, $cursor);
        $hasMore = count($rawPosts) > self::LIMIT;

        if ($hasMore) {
            array_pop($rawPosts);
        }

        $lastPost = end($rawPosts);
        $nextCursor = ($hasMore && $lastPost)
            ? ($lastPost->retweetedAt ?? $lastPost->createdAt)->format(\DateTimeInterface::ATOM)
            : null;

        return [
            'user' => $user,
            'posts' => $rawPosts,
            'nextCursor' => $nextCursor,
            'hasMore' => $hasMore,
            'replies' => $this->replyRepository->getByUserId($userId),
            'likedPosts' => $this->likeRepository->getLikedPostsByUserId($userId, $authUserId),
            'followers' => $this->followRepository->getFollowers($userId, $authUserId),
            'following' => $this->followRepository->getFollowing($userId, $authUserId),
        ];
    }
}
