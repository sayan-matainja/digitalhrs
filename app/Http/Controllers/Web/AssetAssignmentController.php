<?php

namespace App\Http\Controllers\Web;

use App\Enum\AssetAssignmentStatusEnum;
use App\Enum\AssetReturnConditionEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\AssetManagement\AssetDetailRequest;
use App\Services\AssetManagement\AssetAssignmentService;
use App\Services\AssetManagement\AssetService;
use App\Services\AssetManagement\AssetTypeService;
use App\Services\Notification\NotificationService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssetAssignmentController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.assetManagement.assetDetail.';

    public function __construct(
        protected AssetService     $assetService,
        protected UserRepository   $userRepo,
        protected CompanyRepository $companyRepository,
        protected AssetAssignmentService $assignmentService,
        protected NotificationService $notificationService,
        protected DepartmentRepository $departmentRepository,
    )
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function index($assetId)
    {
        try {

            $assetDetail = $this->assetService->findAssetById($assetId,['id','name']);

            $with = ['asset:id,name','user:id,name'];
            $select = ['*'];
            $assignmentList = $this->assignmentService->getAssignmentsPaginated($assetId, $select, $with);
            return view($this->view . 'assignment_list', compact('assignmentList','assetDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }




    public function create($branchId)
    {
        try {
            $departments = $this->departmentRepository->getAllActiveDepartmentsByBranchId($branchId,[], ['id', 'dept_name']);
            return response()->json([
                'data' => $departments,
            ], 200);
        } catch (Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
            ], $exception->getCode() ?: 500);
        }
    }

    public function store(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'asset_id' => 'required|exists:assets,id',
                'user_id' => 'required|exists:users,id',
                'branch_id' => 'nullable|exists:branches,id',
                'department_id' => 'required|exists:departments,id',
                'assigned_date' => 'required|date',
            ]);


            $assetDetail = $this->assetService->findAssetById($id);
            if (!auth('admin')->check() && auth()->check()) {
                $validatedData['branch_id'] = $assetDetail->branch_id;
            }
            if ($assetDetail->is_working != 'yes') {
               if($assetDetail->is_working == 'no'){
                    $errormessage = 'Asset ' . $assetDetail->name . ' is not working' ;
                } else{
                   $errormessage ='Asset ' . $assetDetail->name . ' is under maintenance';
               }
                return response()->json([
                    'error' => $errormessage,
                ], 400);
            }
            if ($assetDetail->is_available == 0) {
                return response()->json([
                    'error' => 'Asset ' . $assetDetail->name . ' is not available',
                ], 400);
            }

            $validatedData['status'] = AssetAssignmentStatusEnum::assigned->value;
            DB::beginTransaction();
             $assetAssignmentDetail = $this->assignmentService->saveDetail($validatedData);

             if($assetAssignmentDetail){
                 $this->assetService->updateAssetDetail($id,[
                     'is_available'=>0
                 ]);
             }
            DB::commit();

            $notificationTitle = __('index.asset_assign_notification');
            $message =  __('message.asset_notification_message', [
                'asset' => $assetDetail->name
            ]);

            $this->sendNotification($id,$notificationTitle, $message,$validatedData['user_id']);

            return response()->json([
                'message' => __('message.asset_assignment_saved'),
            ], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            return response()->json([
                'error' => $exception->getMessage(),
            ], $exception->getCode() ?: 500);
        }
    }
    private function sendNotification($id,$title,$message, $userId)
    {
        $notificationData['title'] = $title;
        $notificationData['type'] = 'asset';
        $notificationData['user_id'] = [$userId];
        $notificationData['description'] = $message;
        $notificationData['notification_for_id'] = $id;
        $notification = $this->notificationService->store($notificationData);
        if($notification){
            SMPushHelper::sendLeaveNotification($title, $message,$userId);

        }

    }


    /**
     * @throws AuthorizationException
     */
//    public function update(AssetDetailRequest $request, $id)
//    {
//        $this->authorize('edit_assets');
//        try {
//            $validatedData = $request->validated();
//            DB::beginTransaction();
//            $this->assetService->updateAssetDetail($id, $validatedData);
//            DB::commit();
//            return redirect()->route('admin.assets.index')
//                ->with('success', __('message.asset_update'));
//        } catch (Exception $exception) {
//            DB::rollBack();
//            return redirect()->back()->with('danger', $exception->getMessage())
//                ->withInput();
//        }
//    }

    /**
     * @throws AuthorizationException
     */
    public function delete($id)
    {
        $this->authorize('delete_assets');
        try {
            DB::beginTransaction();
                $this->assetService->deleteAsset($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.asset_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function returnlist()
    {
        try {
            $select = ['*'];
            $with = ['asset.type:id,name','user:id,name'];
            $returnLists = $this->assignmentService->getReturnAssetsPaginated($select,$with);
            $maintenanceLists = $this->assignmentService->getMaintenanceAssetsPaginated($select,$with);

            return view($this->view . 'return_list', compact('returnLists','maintenanceLists'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function storeReturn(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'is_working' => 'required|in:yes,no',
                'notes' => [
                    'nullable',
                    'string'
                ],
            ]);
            $validatedData['return_condition'] = $validatedData['is_working'] == 'yes' ? AssetReturnConditionEnum::working->value :  AssetReturnConditionEnum::requireMaintenance->value ;
            $validatedData['returned_date'] = date('Y-m-d');
            $validatedData['status'] = AssetAssignmentStatusEnum::returned->value;

            DB::beginTransaction();
            $assetAssignmentDetail = $this->assignmentService->updateDetail($id, $validatedData);

            $assetUpdate = [
                'is_available' =>  $validatedData['is_working'] == 'yes' ? 1 : 0,
                'is_working' =>  $validatedData['is_working']
            ];
            $this->assetService->updateAssetDetail($assetAssignmentDetail->asset_id,$assetUpdate);
            DB::commit();


            return redirect()->route('admin.assets.index')->with('success', __('message.asset_assignment_return'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }



    /**
     * @throws AuthorizationException
     */
    public function changeRepairStatus($id)
    {
        $this->authorize('edit_assets');
        try {
            DB::beginTransaction();
            $this->assignmentService->toggleRepairStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


}
