<?php

namespace App\Actions\Teams;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * チームを新規作成してオーナーとして登録するアクション。
 */
class CreateTeam
{
    /**
     * チームを作成してユーザーをオーナーとして追加する。
     *
     * @param  User  $user  オーナーになるユーザー
     * @param  string  $name  チーム名
     * @param  bool  $isPersonal  パーソナルチームか否か
     * @return Team 作成されたチーム
     */
    public function handle(User $user, string $name, bool $isPersonal = false): Team
    {
        return DB::transaction(function () use ($user, $name, $isPersonal) {
            $team = Team::create([
                'name' => $name,
                'is_personal' => $isPersonal,
            ]);

            $team->memberships()->create([
                'user_id' => $user->id,
                'role' => TeamRole::Owner,
            ]);

            $user->switchTeam($team);

            return $team;
        });
    }
}
