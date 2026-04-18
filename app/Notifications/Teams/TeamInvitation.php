<?php

namespace App\Notifications\Teams;

use App\Infrastructure\Eloquent\Models\TeamInvitation as TeamInvitationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * チーム招待メールを送信する通知クラス。
 */
class TeamInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * 招待モデルを受け取って通知を作成する。
     *
     * @param  TeamInvitationModel  $invitation  招待モデル
     */
    public function __construct(public TeamInvitationModel $invitation)
    {
        //
    }

    /**
     * 通知の配信チャンネルを返す。
     *
     * @param  object  $notifiable  通知先オブジェクト
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * メール通知の内容を生成する。
     *
     * @param  object  $notifiable  通知先オブジェクト
     */
    public function toMail(object $notifiable): MailMessage
    {
        $team = $this->invitation->team;
        $inviter = $this->invitation->inviter;

        return (new MailMessage)
            ->subject(__("You've been invited to join :teamName", ['teamName' => $team->name]))
            ->line(__(':inviterName has invited you to join the :teamName team.', [
                'inviterName' => $inviter->name,
                'teamName' => $team->name,
            ]))
            ->action(__('Accept invitation'), url("/invitations/{$this->invitation->code}/accept"));
    }

    /**
     * 通知の配列表現を返す。
     *
     * @param  object  $notifiable  通知先オブジェクト
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'team_id' => $this->invitation->team_id,
            'team_name' => $this->invitation->team->name,
            'role' => $this->invitation->role->value,
        ];
    }
}
