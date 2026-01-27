<?php

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;

function send_notification_FCM($fcm_token, $title, $body) {
    // 1. Path to your JSON credentials
    $credentialsFilePath = storage_path('app/firebase_credentials.json');

    // 2. Initialize Google Credentials to get the Access Token
    $credentials = new ServiceAccountCredentials(
        ['https://www.googleapis.com/auth/firebase.messaging'],
        $credentialsFilePath
    );

    // Get the actual OAuth 2.0 token
    $accessToken = $credentials->fetchAuthToken()['access_token'];

    // 3. Read Project ID automatically from the JSON file
    $json = json_decode(file_get_contents($credentialsFilePath), true);
    $projectId = $json['project_id'];

    // 4. Send the request to the new FCM V1 API
    $apiUrl = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

    $payload = [
        'message' => [
            'token' => $fcm_token,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
            // Optional: Android specific settings
            'android' => [
                'priority' => 'high',
            ],
            // Optional: iOS specific settings
            'apns' => [
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                    ],
                ],
            ],
        ],
    ];

    $response = Http::withToken($accessToken)
        ->withHeaders(['Content-Type' => 'application/json'])
        ->post($apiUrl, $payload);

    if ($response->successful()) {
        return $projectId;
        return true;
    } else {
        // Log the error for debugging
        \Log::error('FCM Send Error: ' . $response->body());
        return false;
    }
}

function send_notification_FCMOld($fcm_token, $title, $message){

//Fcm Token push Notification
            $FcmTokenArray = [];
            // $FcmTokenArray[] = 'e8a9g6k0SDqcTmpRC8V97p:APA91bGytdS9QrRxtn8kZaP17aBXAtYVqzUzEr76oJukUBWmOZeKPn-qyqQofXDySb9ZcEHhJCw4Wtho2DovN-FA2ZHfk1G0nMz2b1c0MXBNPnCLPXuSJnAwy4leZ2Q8B9e2IHK6pX0U';
            $FcmTokenArray[] = $fcm_token;

            //Push Notification
            $url = 'https://fcm.googleapis.com/fcm/send';
            $FcmToken = $FcmTokenArray;
            $serverKey = env( 'FCM_KEY' );
            // $serverKey = 'AAAALF-9WUQ:APA91bG9KKkBJmUEYxULtQy_BYQh8QGsZ3DEPqRDfywA0KlxAs9oIKPL51mibsNEloyoGcxX8ZGE_SF-O0DGa-rGJWc5VOjy_UXygMhMotCKuo6pbolFbMDm2l_UUhfMr9n1P2_lZ8ds';
            $data = [
                "registration_ids" => $FcmToken,
                "notification" => [
                    // "title" => $senderData->first_name." ".$senderData->last_name,
                    "title" => $title,
                    "body" => $message,
                ],
                "data" => [
                    // "title" => $senderData->first_name." ".$senderData->last_name,
                    "title" => $title,
                    "body" => $message,
                ]
            ];
            $encodedData = json_encode($data);

            $headers = [
                'Authorization:key=' . $serverKey,
                'Content-Type: application/json',
            ];
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

            $result = curl_exec($ch);
            curl_close($ch);
// End

}
