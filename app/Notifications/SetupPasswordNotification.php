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

        return (new MailMessage)
            ->subject('Set Up Your Password - QIS')
            ->view('email.setup_password', ['setupUrl' => $url]);
    }
}
