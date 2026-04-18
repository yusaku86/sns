<?php

namespace App\Rules;

use App\Models\Team;
use App\Models\TeamInvitation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * チームへの重複招待（既存メンバーや保留中招待）を検証するルール。
 */
class UniqueTeamInvitation implements ValidationRule
{
    public function __construct(protected Team $team)
    {
        //
    }

    /**
     * バリデーションルールを実行する。
     *
     * @param  string  $attribute  フィールド名
     * @param  mixed  $value  入力値（メールアドレス）
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail  エラーコールバック
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = strtolower($value);

        $isMember = $this->team->members()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->exists();

        if ($isMember) {
            $fail(__('This user is already a member of the team.'));

            return;
        }

        $hasPendingInvitation = TeamInvitation::where('team_id', $this->team->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($hasPendingInvitation) {
            $fail(__('An invitation has already been sent to this email address.'));
        }
    }
}
