<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * リプライ作成リクエストのバリデーション。
 */
class StoreReplyRequest extends FormRequest
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
            'content' => ['required', 'string', 'max:140'],
        ];
    }
}
