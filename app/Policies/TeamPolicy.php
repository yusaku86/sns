<?php

namespace App\Policies;

use App\Enums\TeamPermission;
use App\Models\Team;
use App\Models\User;

/**
 * チームリソースへのアクセス制御ポリシー。
 */
class TeamPolicy
{
    /**
     * 任意のチームを閲覧できるか判定する（全ユーザー許可）。
     *
     * @param  User  $user  認証ユーザー
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * 指定チームを閲覧できるか判定する。
     *
     * @param  User  $user  認証ユーザー
     * @param  Team  $team  対象チーム
     */
    public function view(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }

    /**
     * チームを作成できるか判定する（全ユーザー許可）。
     *
     * @param  User  $user  認証ユーザー
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * 指定チームを更新できるか判定する。
     *
     * @param  User  $user  認証ユーザー
     * @param  Team  $team  対象チーム
     */
    public function update(User $user, Team $team): bool
    {
        return $user->hasTeamPermission($team, TeamPermission::UpdateTeam);
    }

    /**
     * チームにメンバーを追加できるか判定する。
     *
     * @param  User  $user  認証ユーザー
     * @param  Team  $team  対象チーム
     */
    public function addMember(User $user, Team $team): bool
    {
        return $user->hasTeamPermission($team, TeamPermission::AddMember);
    }

    /**
     * チームメンバーのロールを更新できるか判定する。
     *
     * @param  User  $user  認証ユーザー
     * @param  Team  $team  対象チーム
     */
    public function updateMember(User $user, Team $team): bool
    {
        return $user->hasTeamPermission($team, TeamPermission::UpdateMember);
    }

    /**
     * チームメンバーを除名できるか判定する。
     *
     * @param  User  $user  認証ユーザー
     * @param  Team  $team  対象チーム
     */
    public function removeMember(User $user, Team $team): bool
    {
        return $user->hasTeamPermission($team, TeamPermission::RemoveMember);
    }

    /**
     * チームに招待を送れるか判定する。
     *
     * @param  User  $user  認証ユーザー
     * @param  Team  $team  対象チーム
     */
    public function inviteMember(User $user, Team $team): bool
    {
        return $user->hasTeamPermission($team, TeamPermission::CreateInvitation);
    }

    /**
     * チームの招待を取り消せるか判定する。
     *
     * @param  User  $user  認証ユーザー
     * @param  Team  $team  対象チーム
     */
    public function cancelInvitation(User $user, Team $team): bool
    {
        return $user->hasTeamPermission($team, TeamPermission::CancelInvitation);
    }

    /**
     * 指定チームを削除できるか判定する（パーソナルチームは削除不可）。
     *
     * @param  User  $user  認証ユーザー
     * @param  Team  $team  対象チーム
     */
    public function delete(User $user, Team $team): bool
    {
        return ! $team->is_personal && $user->hasTeamPermission($team, TeamPermission::DeleteTeam);
    }
}
