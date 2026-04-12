<?php

namespace App\Models;

use App\Infrastructure\Eloquent\Models\Follow;
use App\Infrastructure\Eloquent\Models\Like;
use App\Infrastructure\Eloquent\Models\Membership;
use App\Infrastructure\Eloquent\Models\Post;
use App\Infrastructure\Eloquent\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;

// Infrastructure層の実装を参照
/**
 * @property string $id
 * @property string $name
 * @property string $handle
 * @property string|null $bio
 * @property string $email
 * @property CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property string|null $current_team_id
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property CarbonImmutable|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Team|null $currentTeam
 * @property-read Collection<int, Follow> $followers
 * @property-read int|null $followers_count
 * @property-read Collection<int, Follow> $followings
 * @property-read int|null $followings_count
 * @property-read Collection<int, Like> $likes
 * @property-read int|null $likes_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Team> $ownedTeams
 * @property-read int|null $owned_teams_count
 * @property-read Collection<int, Post> $posts
 * @property-read int|null $posts_count
 * @property-read Collection<int, Membership> $teamMemberships
 * @property-read int|null $team_memberships_count
 * @property-read Collection<int, Team> $teams
 * @property-read int|null $teams_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCurrentTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereHandle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class User extends \App\Infrastructure\Eloquent\Models\User {}
