<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * ユーザープロフィールのバリデーションルールを提供するトレイト。
 */
trait ProfileValidationRules
{
    /**
     * プロフィール（名前・メール）のバリデーションルールを返す。
     *
     * @param  string|int|null  $userId  自身の更新時にuniqueチェックから除外するユーザーID
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function profileRules(string|int|null $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * ユーザー名のバリデーションルールを返す。
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * メールアドレスのバリデーションルールを返す。
     *
     * @param  string|int|null  $userId  uniqueチェックから除外するユーザーID
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function emailRules(string|int|null $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
