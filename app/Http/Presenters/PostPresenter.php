<?php

namespace App\Http\Presenters;

use App\Domain\Post\Entities\Post;
use App\Domain\Post\Entities\PostImage;
use Illuminate\Support\Facades\Storage;

/**
 * 投稿エンティティをフロントエンド向け配列に変換するプレゼンター。
 */
class PostPresenter
{
    /**
     * 投稿エンティティをフロントエンド向け配列に変換する。
     * 画像パスを署名付き一時URLに置き換える。
     *
     * @param  Post  $post  変換する投稿エンティティ
     * @return array<string, mixed>
     */
    public static function toArray(Post $post): array
    {
        $data = $post->jsonSerialize();
        $data['images'] = array_map(
            fn (PostImage $img) => [
                'id' => $img->id,
                'url' => Storage::disk('local')->temporaryUrl($img->path, now()->addHour()),
                'order' => $img->order,
            ],
            $post->images,
        );

        return $data;
    }

    /**
     * 投稿エンティティの配列をフロントエンド向け配列のコレクションに変換する。
     *
     * @param  Post[]  $posts  変換する投稿エンティティの配列
     * @return array[]
     */
    public static function collection(array $posts): array
    {
        return array_map(fn (Post $post) => self::toArray($post), $posts);
    }
}
