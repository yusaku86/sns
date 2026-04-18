<?php

namespace App\Support;

/**
 * ユーザーが所属するチームの情報を保持する読み取り専用クラス。
 */
readonly class UserTeam
{
    /**
     * @param  string  $id  チームID
     * @param  string  $name  チーム名
     * @param  string  $slug  チームスラッグ
     * @param  bool  $isPersonal  個人チームかどうか
     * @param  string|null  $role  ロール値
     * @param  string|null  $roleLabel  ロール表示名
     * @param  bool|null  $isCurrent  現在選択中のチームかどうか
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public bool $isPersonal,
        public ?string $role,
        public ?string $roleLabel,
        public ?bool $isCurrent = null,
    ) {
        //
    }
}
