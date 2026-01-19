<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Resources\Notification\NotificationCollection;
use App\Services\Notification\NotificationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotificationApiController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function getAllRecentPublishedNotification(Request $request): JsonResponse|NotificationCollection
    {
        try {
            $select = ['*'];
            $perPage = $request->get('per_page') ?? 20;
            $notifications = $this->notificationService->getAllCompanyRecentActiveNotification($perPage, $select);
            return new NotificationCollection($notifications);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function changeUserNotificationSeenStatus($notificationId): JsonResponse
    {
        try {
            $userNotificationDetail = $this->notificationService->changeUserNotificationToSeen($notificationId);
            return AppHelper::sendSuccessResponse(__('index.updated_successfully'), $userNotificationDetail);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
        }

    }

}
