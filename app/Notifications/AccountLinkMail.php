<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use App\Models\Account\AccountLink;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as LaravelNotification;

class AccountLinkMail extends LaravelNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  AccountLink  $accountLink
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(AccountLink $accountLink): MailMessage
    {
        $user = $accountLink->invitedBy;
        $acceptLinkUrl = $this->acceptAccountLinkUrl($accountLink);

        return (new MailMessage)
            ->subject(trans('mail.account_link_title', ['name' => $user->name]))
            ->line(trans('mail.account_link_intro', ['name' => $user->name, 'email' => $user->email]))
            ->line(trans('mail.account_link_explanation'))
            ->line(trans('mail.account_link_link'))
            ->action(trans('mail.account_link_button'), $acceptLinkUrl)
            ->line(trans('mail.account_link_expiration', ['count' => Config::get('auth.invitation.expire', 2)]));
    }

    /**
     * Get the account link acceptance URL.
     *
     * @param  AccountLink  $accountLink
     * @return string
     */
    protected function acceptAccountLinkUrl(AccountLink $accountLink)
    {
        return URL::temporarySignedRoute(
            'account-links.accept',
            now()->addDays(Config::get('auth.invitation.expire', 2)),
            ['key' => $accountLink->invitation_key]
        );
    }
}
