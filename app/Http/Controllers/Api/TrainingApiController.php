<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Resources\Training\TrainingCollection;
use App\Resources\Training\TrainingResource;
use App\Services\TrainingManagement\TrainingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainingApiController extends Controller
{

    public function __construct(protected TrainingService $trainingService)
    {}

    public function getAllTrainings(Request $request)
    {
        try {
            $select = ['*'];
            $with = ['trainingType:id,title','employeeTraining.employee:id,name','branch:id,name','trainingInstructor.trainer.employee.department:id,dept_name','trainingDepartment.department:id,dept_name'];
            $perPage = $request->get('per_page') ?? 20;
            $isUpcoming =$request->get('is_upcoming') ?? 1;
            $trainingDetail = $this->trainingService->getApiTraining($perPage,$select, $with,$isUpcoming);

            return new TrainingCollection($trainingDetail);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function findTrainingDetail($trainingId)
    {
        try {
            $with = ['trainingType:id,title','employeeTraining.employee:id,name','branch:id,name','trainingInstructor.trainer.employee:id,name','trainingDepartment.department:id,dept_name'];

            $trainingDetail = $this->trainingService->findTrainingById($trainingId,['*'], $with);
            if(!$trainingDetail){
                return AppHelper::sendErrorResponse('Event Not Found', 400);
            }
            $detail = new TrainingResource($trainingDetail);
            return AppHelper::sendSuccessResponse(__('index.data_found'), $detail);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

}

