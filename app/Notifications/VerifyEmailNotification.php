<?php

namespace App\Notifications;

use App\Models\InternalUser;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Lang;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = $this->verificationUrl($notifiable);

        // Set the locale for this email (if the user has a language preference)
        if (method_exists($notifiable, 'getLocale')) {
            app()->setLocale($notifiable->getLocale());
        }

        // Use the custom view
        return (new MailMessage)
            ->subject(Lang::get('Verify Your Email Address'))
            ->view('email.verify', [
                'url' => $url,
                'user' => $notifiable,
            ]);
    }

    protected function verificationUrl($notifiable)
    {
        $guard = $notifiable instanceof InternalUser ? 'internal' : 'public';

        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
                'guard' => $guard,
            ]
        );
    }
}