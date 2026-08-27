<?php

namespace App\Http\Controllers;

use App\Mail\ApplicationMail;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    public function sendStatusMessage($fullname, $type, $applicationId, $status, $messageText, $phoneNumber)
    {
        $normalizedPhone = preg_replace('/^\+/', '', $phoneNumber);

        $user = PublicUser::where('phone_number', $phoneNumber)
            ->orWhere('phone_number', $normalizedPhone)
            ->orWhere('phone_number', '+' . $normalizedPhone)
            ->first();

        if ($user) {
            $msgEn = "Your {$type} application (ID: {$applicationId}) status has been updated to {$status}. {$messageText}";
            $msgBm = "Status permohonan {$type} anda (ID: {$applicationId}) telah dikemaskini kepada {$status}. {$messageText}";
            
            $user->notify(new \App\Notifications\ApplicationNotification($msgEn, $msgBm, 'System', '#'));
        }

        // $whatsappSuccess = $this->sendWhatsapp($fullname, $type, $applicationId, $status, $messageText, $phoneNumber);

        // if (!$whatsappSuccess) {
        //     Log::warning('WhatsApp failed, falling back to email only', [
        //         'phone_number' => $phoneNumber,
        //         'application_id' => $applicationId,
        //     ]);
        // }

        // // Always send email regardless of WhatsApp status
        // $this->sendEmail($fullname, $type, $applicationId, $status, $messageText, $phoneNumber);
    }

    private function sendWhatsapp($fullname, $type, $applicationId, $status, $messageText, $phoneNumber)
    {
        // try {
        //     $phoneNumber = preg_replace('/^\+/', '', $phoneNumber);

        //     $url = 'https://rest.moceanapi.com/rest/2/send-message/whatsapp';

        //     $bearerToken = config('services.mocean.token');
        //     $fromNumber = config('services.mocean.from');

        //     $payload = [
        //         'mocean-from' => $fromNumber,
        //         'mocean-to' => $phoneNumber,
        //         'mocean-event-url' => '',
        //         'mocean-content' => [
        //             'type' => 'template',
        //             'wa_template' => [
        //                 'name' => 'qisapplicationstatus',
        //                 'language' => 'en',
        //                 'body_params' => [
        //                     ['type' => 'text', 'text' => $fullname],
        //                     ['type' => 'text', 'text' => $type],
        //                     ['type' => 'text', 'text' => $applicationId],
        //                     ['type' => 'text', 'text' => $status],
        //                     ['type' => 'text', 'text' => $messageText],
        //                 ],
        //                 'wa_buttons' => [
        //                     [
        //                         'type' => 'url',
        //                         'index' => 0,
        //                         'url_parameter' => $applicationId,
        //                     ],
        //                 ],
        //             ],
        //         ],
        //     ];

        //     $response = Http::withToken($bearerToken)
        //         ->timeout(10)           // Give up after 10 seconds
        //         ->retry(2, 1000)        // Retry 2 times, 1 second apart
        //         ->post($url, $payload);

        //     if ($response->successful()) {
        //         Log::info('WhatsApp sent successfully', [
        //             'phone_number' => $phoneNumber,
        //             'application_id' => $applicationId,
        //         ]);
        //         return true;
        //     } else {
        //         Log::warning('WhatsApp API returned error', [
        //             'phone_number' => $phoneNumber,
        //             'application_id' => $applicationId,
        //             'response' => $response->body(),
        //         ]);
        //         return false;
        //     }
        // } catch (\Exception $e) {
        //     Log::error('WhatsApp exception: ' . $e->getMessage(), [
        //         'phone_number' => $phoneNumber,
        //         'application_id' => $applicationId,
        //     ]);
        //     return false;
        // }
    }

    private function sendEmail($fullname, $type, $applicationId, $status, $messageText, $phoneNumber)
    {
        $normalizedPhone = preg_replace('/^\+/', '', $phoneNumber);

        $user = PublicUser::where('phone_number', $phoneNumber)
            ->orWhere('phone_number', $normalizedPhone)
            ->orWhere('phone_number', '+' . $normalizedPhone)
            ->first();

        if (!$user) {
            Log::warning('sendEmail: No user found for phone number', [
                'phone_number' => $phoneNumber,
            ]);
            return;
        }

        if (!$user->email) {
            Log::warning('sendEmail: User has no email', [
                'uuid' => $user->uuid,
            ]);
            return;
        }

        try {
            $message = "Dear {$fullname}, your {$type} application (ID: {$applicationId}) status has been updated to {$status}. {$messageText}";

            $title = "QIS Application Update - {$type} #{$applicationId}";

            Mail::to($user->email)->send(
                new ApplicationMail($title, $message)
            );


            Log::info('Email sent successfully', [
                'uuid'           => $user->uuid,
                'email'          => $user->email,
                'application_id' => $applicationId,
                'status'         => $status,
            ]);
        } catch (\Throwable $e) {
            Log::error('Email sending failed', [
                'uuid'           => $user->uuid,
                'email'          => $user->email,
                'application_id' => $applicationId,
                'error'          => $e->getMessage(),
            ]);
        }
    }

    // public function sendStatusMessage($fullname, $type, $applicationId, $status, $messageText, $phoneNumber)
    // {

    //     'test';
    // }
}
