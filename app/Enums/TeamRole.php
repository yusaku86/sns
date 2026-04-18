<?php

namespace App\Enums;

/**
 * チームメンバーのロールを表す列挙型。
 */
enum TeamRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';

    /**
     * ロールの表示名を返す。
     */
    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * ロールが持つ権限の一覧を返す。
     *
     * @return array<TeamPermission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Owner => TeamPermission::cases(),
            self::Admin => [
                TeamPermission::UpdateTeam,
                TeamPermission::CreateInvitation,
                TeamPermission::CancelInvitation,
            ],
            self::Member => [],
        };
    }

    /**
     * 指定権限を持っているか確認する。
     *
     * @param  TeamPermission  $permission  確認する権限
     */
    public function hasPermission(TeamPermission $permission): bool
    {
        return in_array($permission, $this->permissions());
    }

    /**
     * ロールの階層レベルを返す。数値が高いほど権限が強い。
     */
    public function level(): int
    {
        return match ($this) {
            self::Owner => 3,
            self::Admin => 2,
            self::Member => 1,
        };
    }

    /**
     * 指定ロール以上の権限を持っているか確認する。
     *
     * @param  TeamRole  $role  比較対象のロール
     */
    public function isAtLeast(TeamRole $role): bool
    {
        return $this->level() >= $role->level();
    }

    /**
     * メンバーに割り当て可能なロール一覧を返す（Owner除く）。
     *
     * @return array<array{value: string, label: string}>
     */
    public static function assignable(): array
    {
        return collect(self::cases())
            ->filter(fn (self $role) => $role !== self::Owner)
            ->map(fn (self $role) => ['value' => $role->value, 'label' => $role->label()])
            ->values()
            ->toArray();
    }
}
