<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;
    public $type;

    public function __construct($token, $email, $type)
    {
        $this->token = $token;
        $this->email = $email;
        $this->type = $type;
    }

    public function build()
    {
        $resetUrl = url("/reset-password/{$this->token}?email={$this->email}&type={$this->type}");

        return $this->subject('Reset Your SYSTEM Password')
            ->view('email.reset_password')
            ->with([
                'resetUrl' => $resetUrl,
            ]);
    }
}

