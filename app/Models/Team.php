<?php

namespace App\Models;

use App\Infrastructure\Eloquent\Models\Membership;
use App\Infrastructure\Eloquent\Models\TeamInvitation;
use App\Infrastructure\Eloquent\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

// Infrastructure層の実装を参照
/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property bool $is_personal
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Collection<int, TeamInvitation> $invitations
 * @property-read int|null $invitations_count
 * @property-read Membership|null $pivot
 * @property-read Collection<int, User> $members
 * @property-read int|null $members_count
 * @property-read Collection<int, Membership> $memberships
 * @property-read int|null $memberships_count
 *
 * @method static \Database\Factories\TeamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereIsPersonal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Team extends \App\Infrastructure\Eloquent\Models\Team {}
