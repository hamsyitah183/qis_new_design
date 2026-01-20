<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NotificationController extends Controller
{
    //
    public function sendStatusMessage($fullname, $type, $applicationId, $status, $messageText, $phoneNumber)
    {
        
        $phoneNumber = preg_replace('/^\+/', '', $phoneNumber);
        // dd($fullname, $type, $applicationId, $status, $messageText, $phoneNumber);

        $url = 'https://rest.moceanapi.com/rest/2/send-message/whatsapp';
        $bearerToken = 'apit-0NYiktzyYJO9bdPHcs7OQ3P9Rfl4tDJh-gxopQ'; 

        $payload = [
            'mocean-from' => '60128083901',
            'mocean-to' => $phoneNumber,
            'mocean-event-url' => '',
            'mocean-content' => [
                'type' => 'template',
                'wa_template' => [
                    'name' => 'qisapplicationstatus',
                    'language' => 'en',
                    'body_params' => [

                        ['type' => 'text', 'text' => $fullname], 
                        ['type' => 'text', 'text' => $type], 
                        ['type' => 'text', 'text' => $applicationId], 
                        ['type' => 'text', 'text' => $status], 
                        ['type' => 'text', 'text' => $messageText]],

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

        $response = Http::withToken($bearerToken)->post($url, $payload);

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
}
