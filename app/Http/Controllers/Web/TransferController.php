<?php

namespace App\Http\Controllers\Web;

use App\Enum\HrSetupStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\OfficeTimeRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use App\Requests\Transfer\TransferRequest;
use App\Services\Notification\NotificationService;
use App\Services\Transfer\TransferService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    use CustomAuthorizesRequests;
    private string $view = 'admin.transfer.';

    public function __construct(
        protected TransferService     $transferService,
        protected UserRepository       $userRepository, protected BranchRepository $branchRepository,
        protected DepartmentRepository $departmentRepository, protected CompanyRepository $companyRepository,
        protected NotificationService $notificationService, protected PostRepository $postRepository, protected OfficeTimeRepository $officeTimeRepository
    )
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_transfer');
        try {
            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'department_id' => $request->department_id ?? null,
                'transfer_date' => $request->transfer_date ?? null,
            ];
            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $select = ['*'];
            $with = ['employee:id,name','branch:id,name','department:id,dept_name'];
            $transferLists = $this->transferService->getAllTransferPaginated($filterParameters,$select, $with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'index', compact('transferLists','companyDetail','filterParameters'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create_transfer');

        try {
            $companyId = AppHelper::getAuthUserCompanyId();
            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $selectBranch = ['id', 'name'];
            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId, $selectBranch);
            $status = HrSetupStatusEnum::cases();

            return view($this->view . 'create', compact('branch', 'isBsEnabled', 'status'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @param TransferRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(TransferRequest $request)
    {
        $this->authorize('create_transfer');

        try {
            $validatedData = $request->validated();


            DB::beginTransaction();
            $transferDetail = $this->transferService->saveTransferDetail($validatedData);
            DB::commit();

            $employee = $this->userRepository->findUserDetailById($validatedData['employee_id'],['*'],['branch:id,name','department:id,dept_name']);

            if ($transferDetail && $validatedData['notification'] == 1) {

                $newBranch = $this->branchRepository->findBranchDetailById($validatedData['branch_id']);
                $newDepartment = $this->departmentRepository->findDepartmentById($validatedData['department_id'],['dept_name']);
                //  notification to transferred employee

                $message = 'Congratulations! We are pleased to inform you that you have been transferred from branch "' . ucfirst($employee?->branch?->name) . '", department "' . ucfirst($employee?->department?->dept_name) . '" to branch "' . ucfirst($newBranch->name) . '", department "' . ucfirst($newDepartment->dept_name) . '" effective ' . \App\Helpers\AppHelper::formatDateForView($transferDetail['transfer_date']) . '.
                    Please log into the system to review the complete transfer details. We appreciate your continued commitment to the organization and wish you success in your new role.';

                $this->sendNotification($message, $validatedData['employee_id']);

            }

            return redirect()->route('admin.transfer.index')->with('success', __('message.add_transfer'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @throws AuthorizationException
     */

    public function show($id)
    {
        $this->authorize('show_transfer');

        try {
            $select = ['*'];
            $with = ['branch:id,name','oldBranch:id,name', 'department:id,dept_name','oldDepartment:id,dept_name', 'createdBy:id,name', 'updatedBy:id,name', 'employee:id,name','oldSupervisor:id,name','supervisor:id,name','oldPost:id,post_name','post:id,post_name','officeTime:id,opening_time,closing_time','oldOfficeTime:id,opening_time,closing_time'];
            $transferDetail = $this->transferService->findTransferById($id, $select, $with);

            return view($this->view . 'show', compact('transferDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @throws AuthorizationException
     */
    public function edit($id)
    {
        $this->authorize('update_transfer');
        try {
            $transferDetail = $this->transferService->findTransferById($id, ['*'],['oldPost:id,post_name','oldSupervisor:id,name','oldOfficeTime:id,opening_time,closing_time']);

            $companyId = AppHelper::getAuthUserCompanyId();

            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $selectBranch = ['id', 'name'];

            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId, $selectBranch);

            // Fetch users by selected departments
            $filteredOldDepartment = isset($transferDetail->branch_id)
                ? $this->departmentRepository->getAllActiveDepartmentsByBranchId($transferDetail->old_branch_id, [], ['id', 'dept_name'])
                : [];
            $filteredDepartment = isset($transferDetail->branch_id)
                ? $this->departmentRepository->getAllActiveDepartmentsByBranchId($transferDetail->branch_id, [], ['id', 'dept_name'])
                : [];

            $select = ['name', 'id'];
            $filteredUsers = isset($transferDetail->old_department_id)
                ? $this->userRepository->getActiveEmployeeOfDepartment($transferDetail->old_department_id, $select)
                : [];


            $filteredPosts = isset($transferDetail->department_id)
                ? $this->postRepository->getAllActivePostsByDepartmentId($transferDetail->department_id, [], ['id', 'post_name'])
                : [];

            $filteredSupervisor = isset($transferDetail->department_id)
                ? $this->userRepository->getAllActiveEmployeeOfDepartment($transferDetail->department_id, ['id','name'])
                : [];
            $filteredOfficeTime = isset($transferDetail->department_id)
                ? $this->officeTimeRepository->getALlActiveOfficeTimeByBranchId($transferDetail->branch_id,['id','opening_time','closing_time'])
                : [];
            $status = HrSetupStatusEnum::cases();
            return view($this->view . 'edit', compact('transferDetail', 'isBsEnabled', 'branch', 'filteredDepartment','filteredOldDepartment', 'filteredUsers', 'status','filteredPosts','filteredSupervisor','filteredOfficeTime'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TransferRequest $request
     * @param int $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(TransferRequest $request, $id): RedirectResponse
    {
        $this->authorize('update_transfer');
        try {

            $validatedData = $request->validated();


            $previousEmployee = $this->transferService->findTransferById($id, ['employee_id','old_branch_id','old_department_id','branch_id','department_id']);

            $previousEmployeeId = $previousEmployee->employee_id;
            $previousEmployeeOldBranchId = $previousEmployee->old_branch_id;


            DB::beginTransaction();
            $transferDetail = $this->transferService->updateTransferDetail($id, $validatedData);
            DB::commit();

            if ($transferDetail && $validatedData['notification'] == 1) {

                if ($previousEmployeeId != $validatedData['employee_id']) {
                    // add notification
                    $newEmployee = $this->userRepository->findUserDetailById($validatedData['employee_id'],['*'],['branch:id,name','department:id,dept_name']);

                    $newBranch = $this->branchRepository->findBranchDetailById($validatedData['branch_id']);
                    $newDepartment = $this->departmentRepository->findDepartmentById($validatedData['department_id'],['dept_name']);
                    //  notification to transferred employee

                    $message = 'Congratulations! We are pleased to inform you that you have been transferred from branch "' . ucfirst($newEmployee?->branch?->name) . '", department "' . ucfirst($newEmployee?->department?->dept_name) . '" to branch "' . ucfirst($newBranch->name) . '", department "' . ucfirst($newDepartment->dept_name) . '" effective ' . \App\Helpers\AppHelper::formatDateForView($transferDetail['transfer_date']) . '.
                    Please log into the system to review the complete transfer details. We appreciate your continued commitment to the organization and wish you success in your new role.';

                    $this->sendNotification($message, $validatedData['employee_id']);

                    // Withdrawal notification
                    $oldBranch = $this->branchRepository->findBranchDetailById($previousEmployeeOldBranchId);
                    $removeMessage = '⚠️ Your transfer to the branch of ' . ucfirst($oldBranch->name) . ' has been withdrawn from consideration. No further action is required from your end.';
                    $this->sendNotification($removeMessage, $previousEmployeeId);

                }else{
                    // change notification
                    $message = '🔄 Your transfer details have been updated. Check your profile for the latest information!';
                    $this->sendNotification($message, $validatedData['employee_id']);
                }

            }
            return redirect()->route('admin.transfer.index')
                ->with('success', __('message.update_transfer'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function delete($id)
    {
        $this->authorize('delete_transfer');
        try {
            DB::beginTransaction();
            $this->transferService->deleteTransfer($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_transfer'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    private function sendNotification($message, $userId)
    {
        SMPushHelper::sendTransferNotification('Transfer Notification', $message, $userId);
    }


    public function getEmployeeAndPostByDepartment($departmentId): JsonResponse|RedirectResponse
    {
        try {

            $select = ['name', 'id'];
            $users = $this->userRepository->getAllActiveEmployeeOfDepartment($departmentId, $select);

            return response()->json([
                'users' => $users,
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

//    /**
//     * @param Request $request
//     * @param $transferId
//     * @return RedirectResponse
//     * @throws AuthorizationException
//     */
//    public function updateTransferStatus(Request $request, $transferId)
//    {
//        $this->authorize('update_transfer');
//
//
//        try {
//
//            $validatedData = $request->validate([
//                'status' => ['required', 'string', Rule::in(array_column(HrSetupStatusEnum::cases(), 'value'))],
//                'remark' => ['required', 'required_if:status,'.HrSetupStatusEnum::rejected->value, 'string', 'min:10'],
//            ]);
//
//            DB::beginTransaction();
//            $transferDetail = $this->transferService->updateStatus($transferId, $validatedData);
//            DB::commit();
//
//            if($transferDetail){
//                $notificationData = [
//                    'title' => 'Transfer Status Update',
//                    'type' => 'Transfer',
//                    'user_id' => [$transferDetail->employee_id],
//                    'description' => 'Your Transfer has been ' . ucfirst($validatedData['status']),
//                    'notification_for_id' => $transferId,
//                ];
//
//                $notification = $this->notificationService->store($notificationData);
//
//                if($notification){
//                    $this->sendNotification($notification['description'],$transferDetail->employee_id);
//                }
//            }
//
//
//            return redirect()
//                ->route('admin.transfer.index')
//                ->with('success', __('message.status_update'));
//        } catch (Exception $exception) {
//            DB::rollBack();
//            return redirect()->back()->with('danger', $exception->getMessage());
//        }
//    }


    /**
     * @param $departmentId
     * @return JsonResponse
     */
    public function getUserTransferDepartmentData($departmentId)
    {
        try {

            $supervisors = $this->userRepository->getAllActiveEmployeeOfDepartment($departmentId, ['id','name']);
            $posts = $this->postRepository->getAllActivePostsByDepartmentId($departmentId, [], ['post_name', 'id']);

            return response()->json([
                'status'=>200,
                'supervisors' => $supervisors,
                'posts' => $posts,
            ]);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }

    }

    /**
     * @param $branchId
     * @return JsonResponse
     */
    public function getUserTransferBranchData($branchId)
    {
        try {

            $departments = $this->departmentRepository->getAllActiveDepartmentsByBranchId($branchId,[], ['id','dept_name']);
            $officeTimes = $this->officeTimeRepository->getALlActiveOfficeTimeByBranchId($branchId, ['id','opening_time','closing_time']);

            return response()->json([
                'departments' => $departments,
                'officeTimes' => $officeTimes,
            ]);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }

    }

    public function getUserTransferData($employeeId)
    {
        try {

            $employee = $this->userRepository->findUserDetailById($employeeId,['id','name','office_time_id','post_id','supervisor_id'],['post:id,post_name','supervisor:id,name','officeTime']);

            return response()->json([
                'post_id' => $employee->post_id,
                'office_time_id' => $employee->office_time_id,
                'supervisor_id' => $employee->supervisor_id,
                'post' => $employee?->post?->post_name,
                'office_time' => $employee?->officeTime?->opening_time .' - '. $employee?->officeTime?->closing_time,
                'supervisor' => $employee?->supervisor?->name,
            ]);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }

    }

}
