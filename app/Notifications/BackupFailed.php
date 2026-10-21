<?php

namespace App\Notifications;

use App\Filament\Pages\Backups;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BackupFailed extends Notification
{
    public function __construct(public string $error) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('Website backup failed: '.config('app.name'))
            ->line('The scheduled backup of '.config('app.url').' did not finish.')
            ->line('**Error:** '.$this->error)
            ->action('Open backups', Backups::getUrl());
    }
}
