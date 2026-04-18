<?php

namespace App\Application\Post;

use App\Domain\Hashtag\Repositories\HashtagRepositoryInterface;
use App\Domain\Post\Entities\Post;
use App\Domain\Post\Repositories\PostImageRepositoryInterface;
use App\Domain\Post\Repositories\PostRepositoryInterface;
use DateTimeImmutable;

/**
 * 投稿を新規作成するユースケース。ハッシュタグの抽出・同期と画像保存も担う。
 */
class CreatePostUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private HashtagRepositoryInterface $hashtagRepository,
        private PostImageRepositoryInterface $postImageRepository,
    ) {}

    /**
     * 投稿を作成して返す。
     *
     * @param  string  $postId  投稿ID（呼び出し元で生成済みのUUID）
     * @param  string  $userId  投稿者のユーザーID
     * @param  string  $userName  投稿者の表示名
     * @param  string  $userHandle  投稿者のハンドル名
     * @param  string  $content  投稿本文
     * @param  string[]  $imagePaths  ストレージ上のパス（order順、最大8件）
     * @return Post 作成された投稿エンティティ
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

    /**
     * 投稿本文からハッシュタグ名を抽出する。
     *
     * @param  string  $content  投稿本文
     * @return string[] ハッシュタグ名の配列（重複なし・#なし）
     */
    private function extractHashtags(string $content): array
    {
        preg_match_all('/#([\w\p{L}]+)/u', $content, $matches);

        return array_values(array_unique($matches[1]));
    }
}
