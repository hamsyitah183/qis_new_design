<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassNotification extends Notification
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
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
            'type' => $this->type,
        ], false));

        return (new MailMessage)
            ->subject('Reset Password Request')
            ->view('email.reset_password', ['resetUrl' => $resetUrl]);
    }
}