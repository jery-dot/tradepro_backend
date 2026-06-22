<?php


function send_notification_FCM($fcm_token, $senderData, $message){

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
                    "title" => $senderData,
                    "body" => $message,
                ],
                "data" => [
                    // "title" => $senderData->first_name." ".$senderData->last_name,
                    "title" => $senderData,
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
