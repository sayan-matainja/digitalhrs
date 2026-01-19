<?php

namespace App\Http\Controllers\Api;

use App\Enum\AssetAssignmentStatusEnum;
use App\Enum\AssetReturnConditionEnum;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use App\Resources\Asset\AssetCollection;
use App\Services\AssetManagement\AssetAssignmentService;
use App\Services\AssetManagement\AssetService;
use App\Services\Notification\NotificationService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetAssignmentApiController extends Controller
{

    public function __construct(
        protected AssetService     $assetService,
        protected UserRepository   $userRepo,
        protected CompanyRepository $companyRepository,
        protected AssetAssignmentService $assignmentService,
        protected NotificationService $notificationService
    )
    {
    }


    public function index()
    {
        try {
            $with = ['asset:id,name','user:id,name'];
            $select = ['*'];
            $assignmentList = $this->assignmentService->getEmployeeAssignmentsPaginated(auth()->user()->id, $select, $with);

            $assetData = new AssetCollection($assignmentList);

            return AppHelper::sendSuccessResponse(__('index.data_found'),$assetData);
        } catch (\Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }

    }


    public function store(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'is_working' => 'required|in:yes,no',
                'notes' => [
                    'nullable',
                    'string'
                ],
            ]);
            $permissionKeyForNotification = 'asset_return_notification';

            $validatedData['return_condition'] = $validatedData['is_working'] == 'yes' ? AssetReturnConditionEnum::working->value :  AssetReturnConditionEnum::requireMaintenance->value ;
            $validatedData['returned_date'] = date('Y-m-d');
            $validatedData['status'] = AssetAssignmentStatusEnum::returned->value;

            DB::beginTransaction();
                $assetAssignmentDetail = $this->assignmentService->updateDetail($id, $validatedData);

                $assetUpdate = [
                    'is_available' =>  $validatedData['is_working'] == 'yes' ? 1 : 0,
                    'is_working' =>  $validatedData['is_working'] == 'yes' ? 'yes' : 'no'
                ];
                $this->assetService->updateAssetDetail($assetAssignmentDetail->asset_id,$assetUpdate);
            DB::commit();


            if ($assetAssignmentDetail) {
                $asset = $this->assetService->findAssetById($assetAssignmentDetail->asset_id,['id','name']);
                $notificationTitle = __('index.asset_assign_notification');
                $notificationMessage = __('index.asset_return_message', [
                    'name' => ucfirst(auth()->user()?->name),
                    'asset' => ucfirst($asset->name),
                    'reasons' => $validatedData['notes']
                ]);
                AppHelper::sendNotificationToAuthorizedUser(
                    $notificationTitle,
                    $notificationMessage,
                    $permissionKeyForNotification
                );
            }
            return AppHelper::sendSuccessResponse(__('message.asset_assignment_return'));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }






}
