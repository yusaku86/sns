<?php

namespace App\Http\Requests\Teams;

use App\Enums\TeamRole;
use App\Rules\UniqueTeamInvitation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * チーム招待作成のフォームリクエスト。
 */
class CreateTeamInvitationRequest extends FormRequest
{
    /**
     * バリデーションルールを返す。
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', new UniqueTeamInvitation($this->route('team'))],
            'role' => ['required', 'string', Rule::enum(TeamRole::class)],
        ];
    }
}
