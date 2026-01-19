<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Resources\Event\EventCollection;
use App\Resources\Event\EventResource;
use App\Services\EventManagement\EventService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventApiController extends Controller
{

    public function __construct(protected EventService $eventService)
    {}

    public function getAllAssignedEvents(Request $request)
    {
        try {
            $select = ['*'];
            $perPage = $request->get('per_page') ?? 20;
            $isUpcomingEvent =$request->get('is_upcoming_event') ?? 1;
            $eventDetail = $this->eventService->getApiEvents($perPage,$select,$isUpcomingEvent);
            return new EventCollection($eventDetail);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function findEventDetail($eventId)
    {
        try {
            $detail = [];
            $eventDetail = $this->eventService->findEventDetailById($eventId);
            if(!$eventDetail){
                return AppHelper::sendErrorResponse('Event Not Found', 400);
            }
            $detail = new EventResource($eventDetail);
            return AppHelper::sendSuccessResponse(__('index.data_found'), $detail);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

}

