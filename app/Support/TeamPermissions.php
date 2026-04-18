<?php

namespace App\Support;

/**
 * チームに対する認証ユーザーの権限セットを保持するバリューオブジェクト。
 */
readonly class TeamPermissions
{
    public function __construct(
        /** チームを更新できるか */
        public bool $canUpdateTeam,
        /** チームを削除できるか */
        public bool $canDeleteTeam,
        /** メンバーを追加できるか */
        public bool $canAddMember,
        /** メンバーのロールを更新できるか */
        public bool $canUpdateMember,
        /** メンバーを除名できるか */
        public bool $canRemoveMember,
        /** 招待を作成できるか */
        public bool $canCreateInvitation,
        /** 招待を取り消せるか */
        public bool $canCancelInvitation,
    ) {
        //
    }
}
