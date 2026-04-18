<?php

namespace App\Rules;

use App\Models\TeamInvitation;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * チーム招待の有効性（有効期限・承認済み・メールアドレス一致）を検証するルール。
 */
class ValidTeamInvitation implements ValidationRule
{
    public function __construct(protected ?User $user)
    {
        //
    }

    /**
     * バリデーションルールを実行する。
     *
     * @param  string  $attribute  フィールド名
     * @param  mixed  $value  入力値（TeamInvitationインスタンス）
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail  エラーコールバック
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof TeamInvitation || ! $this->user instanceof User) {
            $fail(__('This invitation was sent to a different email address.'));

            return;
        }

        if ($value->isAccepted()) {
            $fail(__('This invitation has already been accepted.'));

            return;
        }

        if ($value->isExpired()) {
            $fail(__('This invitation has expired.'));

            return;
        }

        if (strtolower($value->email) !== strtolower($this->user->email)) {
            $fail(__('This invitation was sent to a different email address.'));
        }
    }
}
