<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\WarningResponse;
use App\Repositories\UserRepository;
use App\Resources\Warning\WarningResource;
use App\Services\Warning\WarningService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;

class WarningApiController extends Controller
{

    public function __construct(protected WarningService $warningService, protected UserRepository $userRepository)
    {
    }


    /**
     * @throws MessagingException
     * @throws FirebaseException
     * @throws AuthorizationException
     */
    public function saveWarningResponse(Request $request, $warningId): JsonResponse
    {
        $this->authorize('warning_response');
        try {
            $user = auth()->user();
            $userId = $user->id;
            $checkResponse = WarningResponse::where([['employee_id', $userId], ['warning_id', $warningId]])->exists();

            if ($checkResponse) {
                throw new Exception(__('index.warning_exist_error'), 404);
            }

            $validator = Validator::make($request->all(), [
                'message' => ['required'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('index.validation_failed'),
                    'errors' => $validator->errors()->toArray()
                ], 422);
            }

            $validatedData = $validator->validated();

            $validatedData['employee_id'] = $userId;

            DB::beginTransaction();
            $warningDetail = $this->warningService->saveWarningResponse($validatedData, $warningId);
            DB::commit();


            if ($warningDetail) {
                $employee = $this->userRepository->findUserDetailById($warningDetail['created_by'], ['id', 'name']);

                $notificationTitle = __('index.warning_notification');
                $notificationMessage = __('index.warning_submit', [
                    'name' => ucfirst($user->name),
                    'subject' =>$warningDetail['subject'],
                    'date' => AppHelper::formatDateForView($warningDetail['warning_date']),
                ]);

                SMPushHelper::sendWarningResponseNotification($notificationTitle, $notificationMessage, $employee->id);

            }
            return AppHelper::sendSuccessResponse(__('index.response_submitted_successfully'));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function getAllWarnings(Request $request)
    {
        $this->authorize('warning_response');
        try {
            $select = ['*'];
            $with = [
                'warningReply' => function ($query) {
                    $query->where('employee_id', getAuthUserCode());
                }
            ];
            $perPage = $request->get('per_page') ?? 20;
            $warningDetail = $this->warningService->getApiWarning($perPage, $select, $with);

            $data = WarningResource::collection($warningDetail);

            return AppHelper::sendSuccessResponse(__('index.data_found'), $data);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

}
