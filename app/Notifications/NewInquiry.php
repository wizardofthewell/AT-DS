<?php

namespace App\Notifications;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewInquiry extends Notification
{
    public function __construct(public Inquiry $inquiry) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $inquiry = $this->inquiry;

        $mail = (new MailMessage)
            ->subject('New website inquiry from '.$inquiry->name)
            ->replyTo($inquiry->email, $inquiry->name)
            ->greeting('New inquiry from '.$inquiry->name)
            ->line('**Email:** '.$inquiry->email);

        if ($inquiry->phone) {
            $mail->line('**Phone:** '.$inquiry->phone);
        }

        if ($inquiry->due_date) {
            $mail->line("**Due date / baby's birthday:** ".$inquiry->due_date->format('F j, Y'));
        }

        if ($inquiry->service_interest) {
            $mail->line('**Interested in:** '.$inquiry->service_interest);
        }

        return $mail
            ->line('---')
            ->line($inquiry->message)
            ->action('Open in admin', InquiryResource::getUrl('edit', ['record' => $inquiry]));
    }
}
