<?php

namespace App\Models;

use App\Enums\TeamRole;
use App\Infrastructure\Eloquent\Models\Team;
use App\Infrastructure\Eloquent\Models\User;
use Carbon\CarbonImmutable;

// Infrastructure層の実装を参照
/**
 * @property string $id
 * @property string $team_id
 * @property string $user_id
 * @property TeamRole $role
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Team|null $team
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Membership extends \App\Infrastructure\Eloquent\Models\Membership {}
