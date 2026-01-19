<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Resources\TeamMeeting\TeamMeetingCollection;
use App\Resources\TeamMeeting\TeamMeetingResource;
use App\Services\TeamMeeting\TeamMeetingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamMeetingApiController extends Controller
{
    private TeamMeetingService $teamMeetingService;

    public function __construct(TeamMeetingService $teamMeetingService)
    {
        $this->teamMeetingService = $teamMeetingService;
    }

    public function getAllAssignedTeamMeetingDetail(Request $request): TeamMeetingCollection|JsonResponse
    {
        try {
            $perPage = $request->get('per_page') ?? 20;
            $teamMeeting = $this->teamMeetingService->getAllAssignedTeamMeetingDetail($perPage);
            return new TeamMeetingCollection($teamMeeting);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function findTeamMeetingDetail($meetingId): JsonResponse
    {
        try {
            $detail = [];
            $teamMeetingDetail = $this->teamMeetingService->findOrFailTeamMeetingDetailById($meetingId);
            $detail[] = new TeamMeetingResource($teamMeetingDetail);
            return AppHelper::sendSuccessResponse(__('index.data_found'), $detail);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

}

