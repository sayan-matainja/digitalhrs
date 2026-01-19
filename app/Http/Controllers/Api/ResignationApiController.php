<?php

namespace App\Http\Controllers\Api;

use App\Enum\ResignationStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Services\Resignation\ResignationService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;

class ResignationApiController extends Controller
{

    public function __construct(protected ResignationService $resignationService, protected UserRepository $userRepository)
    {}


    /**
     * @throws MessagingException
     * @throws FirebaseException
     * @throws AuthorizationException
     */
    public function saveResignationDetail(Request $request): JsonResponse
    {
        $this->authorize('add_resignation');
        try {
            $validator = Validator::make($request->all(), [
                'last_working_day' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:tomorrow'],
                'reason' => ['required'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('index.validation_failed'),
                    'errors' => $validator->errors()->toArray()
                ],422);
            }

            $validatedData = $validator->validated();

            $validatedData['employee_id'] = getAuthUserCode();
            $validatedData['branch_id'] = auth()->user()?->branch_id;
            $validatedData['department_id'] = auth()->user()?->department_id;
            $validatedData['resignation_date'] = Carbon::today();
            $validatedData['status'] = ResignationStatusEnum::pending->value;

            DB::beginTransaction();
              $resignationDetail = $this->resignationService->saveResignationDetail($validatedData);
            DB::commit();

            if($resignationDetail) {
                $employee = $this->userRepository->findUserDetailById(getAuthUserCode(),['id','supervisor_id','name']);

                $notificationTitle = __('index.resignation_notification');
                $notificationMessage = __('index.resignation_submit', [
                    'name' => ucfirst(auth()->user()->name),
                    'resignation_date' => AppHelper::formatDateForView($resignationDetail['resignation_date']),
                    'last_working_day' => AppHelper::formatDateForView($resignationDetail['last_working_day']),
                ]);

                SMPushHelper::sendResignationStatusNotification($notificationTitle, $notificationMessage,$employee->supervisor_id);

            }
            return AppHelper::sendSuccessResponse(__('index.resignation_submitted_successfully'));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function resignationDetail(): JsonResponse
    {
        $this->authorize('add_resignation');
        try {

            $resignationDetail = $this->resignationService->findResignationByEmployeeId(getAuthUserCode());
            if(!$resignationDetail){
                return AppHelper::sendErrorResponse('Resignation Detail Not Found', 400);
            }
            return AppHelper::sendSuccessResponse(__('index.data_found'), $resignationDetail);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

}
