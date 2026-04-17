<?php

namespace App\Http\Presenters;

use App\Domain\Post\Entities\Post;
use App\Domain\Post\Entities\PostImage;
use Illuminate\Support\Facades\Storage;

class PostPresenter
{
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
     * @param  Post[]  $posts
     * @return array[]
     */
    public static function collection(array $posts): array
    {
        return array_map(fn (Post $post) => self::toArray($post), $posts);
    }
}
