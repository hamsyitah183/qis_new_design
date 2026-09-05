<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SetupPasswordNotification extends Notification
{
    use Queueable;

    public $token;
    public $email;
    public $type;

    public function __construct($token, $email, $type)
    {
        $this->token = $token;
        $this->email = $email;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = route('password.setup.form', [
            'token' => $this->token,
            'email' => $this->email,
            'type'  => $this->type,
        ]);

        if (method_exists($notifiable, 'getLocale')) {
            app()->setLocale($notifiable->getLocale());
        }

        $locale = app()->getLocale();
        $viewName = $locale === 'bm' ? 'email.setup_password_bm' : 'email.setup_password_en';
        $subject = $locale === 'bm' ? 'Tetapkan Kata Laluan Anda - QIS' : 'Set Up Your Password - QIS';

        return (new MailMessage)
            ->subject($subject)
            ->view($viewName, ['setupUrl' => $url]);
    }
}
