<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\TimeLeaveRepository;
use App\Resources\Leave\LeaveTypeCollection;
use Exception;
use Illuminate\Http\JsonResponse;

class LeaveTypeApiController extends Controller
{

    public function __construct(protected LeaveTypeRepository $leaveTypeRepo, protected TimeLeaveRepository $timeLeaveRepository)
    {}

    public function getAllLeaveTypeWithEmployeeLeaveRecord(): JsonResponse
    {
        try {
            $filterParameters = AppHelper::leaveYearDetailToFilterData();

            $leaveType = $this->leaveTypeRepo->getAllLeaveTypesWithLeaveTakenbyEmployee($filterParameters);

            $timeLeave = $this->timeLeaveRepository->getTimeLeaveWithLeaveTakenbyEmployee($filterParameters);

            $getAllLeaveType = new LeaveTypeCollection($leaveType);

            $timeLeaveCollection = collect([$timeLeave]);

            $mergedCollection = $getAllLeaveType->merge($timeLeaveCollection);

            return AppHelper::sendSuccessResponse(__('index.data_found'), $mergedCollection);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

}
