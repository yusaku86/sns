<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:140'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpeg,png,gif,webp', 'mimetypes:image/jpeg,image/png,image/gif,image/webp', 'max:10240'],
        ];
    }

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
