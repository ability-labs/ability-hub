<?php

namespace App\Notifications;

use App\Models\RegistrationCode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationCodeCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public RegistrationCode $registrationCode)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(config('app.name') . ' ' . __('Registration Code'))
            ->line(__('You are invited to register to') . ' ' . config('app.name'))
            ->line(__('You may accept this invitation by clicking the button below:'))
            ->action(__('Register'), route('register', ['code' => $this->registrationCode->code ]))
            ->line(__('Registration Code') . ': ' . $this->registrationCode->code)
            ->line('Thanks,');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
