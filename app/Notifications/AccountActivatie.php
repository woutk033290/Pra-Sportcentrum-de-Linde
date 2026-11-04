<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountActivatie extends Notification
{
    public function __construct(public string $token) {}

    /** @return array<int,string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Activeer je account bij De Linde')->greeting('Welkom bij De Linde!')
            ->line('Je bent uitgenodigd als '.$notifiable->rol.'. Stel zelf je wachtwoord in.')
            ->action('Account activeren', route('activatie', ['token' => $this->token]))
            ->line('Deze persoonlijke link is 48 uur geldig en kan één keer worden gebruikt.')->salutation('Sportcentrum De Linde');
    }
}
