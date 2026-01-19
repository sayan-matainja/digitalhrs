<?php

namespace App\Helpers\SMPush;

use App\Helpers\AppHelper;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;

class SMPushNotification
{
    /**
     * @throws MessagingException
     * @throws FirebaseException
     */
    public static function smSend(string $title,
                                  string $message,
                                  array  $data,
                                  array  $recipients,
                                  bool   $isSilence = false): void
    {
        $data['android_channel_id'] = 'ahpu_channel_11';

        $firebase = (new Factory)
            ->withServiceAccount(storage_path('firebase-adminsdk.json'));


        $fromArray = $isSilence ? [] : [
            'notification' => [
                'title' => $title,
                'body' => $message,

            ],
        ];

        $message = CloudMessage
            ::fromArray($fromArray)
            ->withData($data)
            ->withApnsConfig(
                ApnsConfig::new()
                    ->withSound('default')
            )
            ->withAndroidConfig(
                AndroidConfig::new()
                    ->withSound('default')
            )
        ;

        $messaging = $firebase->createMessaging();
        try {
            $status = $messaging->sendMulticast($message, $recipients);

            Log::info('Firebase multicast summary', [
                'success_count' => $status->successes()->count(),
                'failure_count' => $status->failures()->count(),
            ]);

        } catch (MessagingException|FirebaseException $e) {
            Log::error('Firebase messaging error: '.$e->getMessage());
        }



    }
}
