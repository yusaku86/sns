<?php

namespace App\Application\User;

use App\Domain\Like\Repositories\LikeRepositoryInterface;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Reply\Repositories\ReplyRepositoryInterface;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;

class GetUserProfileUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PostRepositoryInterface $postRepository,
        private ReplyRepositoryInterface $replyRepository,
        private LikeRepositoryInterface $likeRepository,
    ) {}

    /**
     * @return array{user: User, posts: array, replies: array, likedPosts: array}|null
     */
    public function execute(string $userId, ?string $authUserId = null): ?array
    {
        $user = $this->userRepository->findById($userId, $authUserId);

        if (! $user) {
            return null;
        }

        return [
            'user' => $user,
            'posts' => $this->postRepository->getByUserId($userId, $authUserId),
            'replies' => $this->replyRepository->getByUserId($userId),
            'likedPosts' => $this->likeRepository->getLikedPostsByUserId($userId, $authUserId),
        ];
    }
}
