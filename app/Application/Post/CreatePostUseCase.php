<?php

namespace App\Application\Post;

use App\Domain\Hashtag\Repositories\HashtagRepositoryInterface;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Str;

class CreatePostUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private HashtagRepositoryInterface $hashtagRepository,
    ) {}

    public function execute(string $userId, string $userName, string $userHandle, string $content): Post
    {
        $post = new Post(
            id: (string) Str::uuid(),
            userId: $userId,
            userName: $userName,
            userHandle: $userHandle,
            content: $content,
            createdAt: new DateTimeImmutable,
            likesCount: 0,
            likedByAuthUser: false,
        );

        $this->postRepository->save($post);

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
