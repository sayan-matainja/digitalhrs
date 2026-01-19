<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushNotificationController extends Controller
{
    public function sendPushNotification(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->all();

            SMPushHelper::sendPushNotification($data['title'], $data['conversation_id'],$data['message'], $data['type'], json_decode($data['usernames']), $data['project_id']??"");

            $response = [
                'status' => true,
                'message' => __('index.successfully_sent_notification'),
                'status_code' => 200,
            ];
            return response()->json($response, 200, $headers = [], $options = 0);
        }catch(\Exception $exception){
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }
}

