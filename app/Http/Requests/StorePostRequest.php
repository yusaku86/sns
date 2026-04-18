<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * 投稿作成リクエストのバリデーション。テキストまたは画像の少なくとも一方を必須とする。
 */
class StorePostRequest extends FormRequest
{
    /**
     * リクエストを認可する。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルールを返す。
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:140'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpeg,png,gif,webp', 'mimetypes:image/jpeg,image/png,image/gif,image/webp', 'max:10240'],
        ];
    }

    /**
     * テキストと画像の両方が未入力の場合にエラーを追加する追加バリデーション。
     *
     * @param  Validator  $validator  バリデーターインスタンス
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $content = $this->input('content');
            $images = $this->file('images');

            $hasContent = filled($content);
            $hasImages = ! empty($images);

            if (! $hasContent && ! $hasImages) {
                $v->errors()->add('content', 'テキストまたは画像のいずれかを入力してください。');
            }
        });
    }
}
