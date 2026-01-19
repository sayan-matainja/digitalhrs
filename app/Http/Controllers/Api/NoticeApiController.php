<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Resources\Notice\NoticeCollection;
use App\Services\Notice\NoticeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoticeApiController extends Controller
{
    private NoticeService $noticeService;

    public function __construct(NoticeService $noticeService)
    {
        $this->noticeService = $noticeService;
    }

    public function getAllRecentlyReceivedNotice(Request $request): JsonResponse|NoticeCollection
    {
        try {
            $perPage = $request->get('per_page') ?? 20;
            $notice = $this->noticeService->getAllReceivedNoticeDetail($perPage);
            return new NoticeCollection($notice);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

}
