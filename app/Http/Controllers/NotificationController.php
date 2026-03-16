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
        $whatsappSuccess = $this->sendWhatsapp($fullname, $type, $applicationId, $status, $messageText, $phoneNumber);

        if (!$whatsappSuccess) {
            Log::warning('WhatsApp failed, falling back to email only', [
                'phone_number' => $phoneNumber,
                'application_id' => $applicationId,
            ]);
        }

        // Always send email regardless of WhatsApp status
        $this->sendEmail($fullname, $type, $applicationId, $status, $messageText, $phoneNumber);
    }

    private function sendWhatsapp($fullname, $type, $applicationId, $status, $messageText, $phoneNumber)
    {
        $phoneNumber = preg_replace('/^\+/', '', $phoneNumber);
        // dd($fullname, $type, $applicationId, $status, $messageText, $phoneNumber);

        $url = 'https://rest.moceanapi.com/rest/2/send-message/whatsapp';
        $bearerToken = 'apit-0NYiktzyYJO9bdPHcs7OQ3P9Rfl4tDJh-gxopQ';
        // $bearerToken = 'apit-QMp24eA8HSNHsMgVRQZ2EpUmZX023tlJ-3ABXC';

        $payload = [
            // 'mocean-from' => '15557785030', //=> DOA
            'mocean-from' => '60128083901', // => Temadigital
            'mocean-to' => $phoneNumber,
            'mocean-event-url' => '',
            'mocean-content' => [
                'type' => 'template',
                'wa_template' => [
                    'name' => 'qisapplicationstatus',
                    'language' => 'en',
                    'body_params' => [['type' => 'text', 'text' => $fullname], ['type' => 'text', 'text' => $type], ['type' => 'text', 'text' => $applicationId], ['type' => 'text', 'text' => $status], ['type' => 'text', 'text' => $messageText]],

                    'wa_buttons' => [
                        [
                            'type' => 'url',
                            'index' => 0,
                            'url_parameter' => $applicationId,
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::withToken($bearerToken)
        ->withoutVerifying() // Disable SSL verification for testing
        ->post($url, $payload);

        if ($response->successful()) {
            return response()->json([
                'status' => 'success',
                'response' => $response->json(),
            ]);
        } else {
            return response()->json(
                [
                    'status' => 'error',
                    'response' => $response->body(),
                ],
                $response->status(),
            );
        }
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
