<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Repositories\DepartmentRepository;
use App\Repositories\SupportRepository;
use App\Requests\Support\SupportStoreRequest;
use App\Resources\Support\SupportQueryListApiCollection;
use App\Resources\Support\SupportResource;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupportApiController extends Controller
{
    use CustomAuthorizesRequests;
    public SupportRepository $supportRepo;
    public DepartmentRepository $departmentRepo;

    public function __construct(SupportRepository $supportRepo,DepartmentRepository $departmentRepo)
    {
        $this->supportRepo = $supportRepo;
        $this->departmentRepo = $departmentRepo;
    }

    /**
     * @throws AuthorizationException
     */
    public function store(SupportStoreRequest $request): JsonResponse
    {
        $this->authorize('query_create');

        try {
            $validatedData = $request->validated();

            $detail = $this->supportRepo->store($validatedData);

            if($detail){
                $notificationTitle = __('index.support_notification');
                $notificationMessage = __('index.support_request_submitted', [
                    'name' => ucfirst($detail->createdBy->name)
                ]);
                AppHelper::sendNotificationToDepartmentHead(
                    $notificationTitle,
                    $notificationMessage,
                    $detail->department_id
                );
            }

            return AppHelper::sendSuccessResponse(
                __('index.query_submitted_successfully'),
                new SupportResource($detail)
            );
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getAuthUserBranchDepartmentLists()
    {
        try{
            $departmentLists = $this->departmentRepo->getDepartmentListUsingAuthUserBranchId();
            return AppHelper::sendSuccessResponse(__('index.data_found'),$departmentLists);
        }catch (Exception $exception){
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getAllAuthUserSupportQueryList(Request $request)
    {
        try{
            $filterParameters = [
                'user_id' => getAuthUserCode(),
                'per_page' => $request->per_page ?? 10
            ];
            $queryLists = $this->supportRepo->getAuthUserSupportQueryListPaginated($filterParameters);
            $data =  new SupportQueryListApiCollection($queryLists);
            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        }catch(Exception $exception){
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

}
