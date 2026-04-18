<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ユーザープロフィール更新リクエストのバリデーション。
 */
class UpdateProfileRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:160'],
            'header_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ];
    }
}
