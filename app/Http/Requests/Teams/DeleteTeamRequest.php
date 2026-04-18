<?php

namespace App\Http\Requests\Teams;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

/**
 * チーム削除のフォームリクエスト。
 */
class DeleteTeamRequest extends FormRequest
{
    /**
     * リクエストを実行する権限があるか確認する。
     */
    public function authorize(): bool
    {
        return Gate::allows('delete', $this->route('team'));
    }

    /**
     * バリデーションルールを返す。
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
        ];
    }

    /**
     * バリデーション後に実行するコールバックを返す。
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $team = $this->route('team');

                if ($this->input('name') !== $team->name) {
                    $validator->errors()->add('name', __('The team name does not match.'));
                }
            },
        ];
    }
}
