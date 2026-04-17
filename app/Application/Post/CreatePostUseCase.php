<?php

namespace App\Application\Post;

use App\Domain\Hashtag\Repositories\HashtagRepositoryInterface;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostImageRepositoryInterface;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use DateTimeImmutable;

class CreatePostUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private HashtagRepositoryInterface $hashtagRepository,
        private PostImageRepositoryInterface $postImageRepository,
    ) {}

    /**
     * @param  string[]  $imagePaths  ストレージ上のパス（order順、最大8件）
     */
    public function execute(string $postId, string $userId, string $userName, string $userHandle, string $content, array $imagePaths = []): Post
    {
        $post = new Post(
            id: $postId,
            userId: $userId,
            userName: $userName,
            userHandle: $userHandle,
            content: $content,
            createdAt: new DateTimeImmutable,
            likesCount: 0,
            likedByAuthUser: false,
        );

        $this->postRepository->save($post);

        if ($imagePaths !== []) {
            $this->postImageRepository->saveForPost($post->id, $imagePaths);
        }

        $hashtags = $this->extractHashtags($content);
        if ($hashtags !== []) {
            $this->hashtagRepository->syncToPost($hashtags, $post->id);
        }

        return $post;
    }

    /** @return string[] */
    private function extractHashtags(string $content): array
    {
        preg_match_all('/#([\w\p{L}]+)/u', $content, $matches);

        return array_values(array_unique($matches[1]));
    }
}
