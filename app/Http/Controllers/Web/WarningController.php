<?php

namespace App\Http\Controllers\Web;

use App\Enum\TrainerTypeEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\WarningEmployee;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\Warning\WarningRequest;
use App\Services\Notification\NotificationService;
use App\Services\Warning\WarningService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class WarningController extends Controller
{
    use CustomAuthorizesRequests;
    private string $view = 'admin.warning.';

    public function __construct(
        protected WarningService $warningService,
        protected UserRepository $userRepository,protected BranchRepository $branchRepository,
        protected DepartmentRepository $departmentRepository, protected CompanyRepository $companyRepository, protected NotificationService $notificationService
    ){}

    /**
     * Display a listing of the resource.
     *
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_warning');
        try{
            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'department_id' => $request->department_id ?? null,
                'warning_date' => $request->warning_date ?? null,
            ];
            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $select = ['*'];
            $with = ['warningEmployee.employee:id,name'];
            $warningLists = $this->warningService->getAllWarningPaginated($filterParameters,$select,$with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view.'index', compact('warningLists','companyDetail','filterParameters'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        $this->authorize('create_warning');

        try{
            $departmentIds = [];
            $employeeIds = [];
            $companyId = AppHelper::getAuthUserCompanyId();
            $selectBranch = ['id','name'];
            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);

            return view($this->view.'create', compact('branch','isBsEnabled','employeeIds','departmentIds'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @param WarningRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(WarningRequest $request)
    {
        $this->authorize('create_warning');

        try{
            $validatedData = $request->validated();

            DB::beginTransaction();
            $warningDetail = $this->warningService->saveWarningDetail($validatedData);
            DB::commit();
            if($warningDetail && $validatedData['notification'] == 1){
                // notification to members
                $message = 'A formal warning has been issued regarding ' . ucfirst($warningDetail['subject']) . '. Please review this notice at your earliest convenience.';

                $this->sendNotification( $warningDetail,$message, $validatedData['employee_id']);

            }

            return redirect()->route('admin.warning.index')->with('success',__('message.add_warning') );
        }catch(Exception $exception){
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
        $this->authorize('show_warning');

        try{
            $select = ['*'];
            $with = ['branch:id,name','warningDepartment.department:id,dept_name','createdBy:id,name', 'updatedBy:id,name','warningEmployee.employee:id,name','warningReply.employee:id,name'];
            $warningDetail = $this->warningService->findWarningById($id,$select,$with);
            $trainerTypes = TrainerTypeEnum::cases();
            $departmentIds = [];
            $employeeIds = [];
            return view($this->view.'show', compact('warningDetail','trainerTypes','departmentIds','employeeIds'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id)
    {
        $this->authorize('update_warning');
        try{
            $with = ['warningEmployee','warningDepartment'];
            $warningDetail = $this->warningService->findWarningById($id,['*'],$with);
            $companyId = AppHelper::getAuthUserCompanyId();

            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $selectBranch = ['id','name'];


            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);

            $selectUser = ['id', 'name'];
            $users = $this->userRepository->getAllVerifiedEmployeeOfCompany($selectUser);
            $employeeIds = [];
            foreach ($warningDetail->warningEmployee as $key => $value) {
                $employeeIds[] = $value->employee_id;
            }

            $departmentIds = [];
            foreach ($warningDetail->warningDepartment as $key => $value) {
                $departmentIds[] = $value->department_id;
            }
            // Fetch users by selected departments
            $filteredDepartment = isset($warningDetail->branch_id)
                ? $this->departmentRepository->getAllActiveDepartmentsByBranchId($warningDetail->branch_id,[], ['id','dept_name'])
                : [];

            $select = ['name', 'id'];
            $filteredUsers = !empty($departmentIds)
                ? $this->userRepository->getActiveEmployeesByDepartment($departmentIds, $select)
                : $users;

            return view($this->view.'edit', compact('warningDetail','isBsEnabled','branch','employeeIds','filteredUsers','departmentIds','filteredDepartment'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param WarningRequest $request
     * @param int $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(WarningRequest $request, $id): RedirectResponse
    {
        $this->authorize('update_warning');
        try{

            $previousEmployee = [];

            $validatedData = $request->validated();

            if($validatedData['notification'] == 1){
                $previousEmployee = WarningEmployee::where('warning_id',$id)->get('employee_id')->toArray();

            }

            DB::beginTransaction();
            $warningDetail = $this->warningService->updateWarningDetail($id,$validatedData);
            DB::commit();

            if($warningDetail && $validatedData['notification'] == 1){

                $previousEmployeeIds = array_column($previousEmployee, 'employee_id');
                $removedIds = array_diff($previousEmployeeIds, $validatedData['employee_id']);
                $addedEmployeeIds = array_diff($validatedData['employee_id'], $previousEmployeeIds);


                $today = date('Y-m-d');
                $start = $validatedData['warning_date'];


                if(strtotime($today) <= strtotime($start)) {
                    // add notification
                    $message = 'A formal warning has been issued regarding ' . ucfirst($warningDetail['subject']) . '. Please review this notice at your earliest convenience.';

                    $this->sendNotification($warningDetail, $message, $addedEmployeeIds);


                    //remove notification
                    $removeMassage = 'The formal warning regarding ' . ucfirst($warningDetail['subject']) . ' has been withdrawn.';
                    $this->sendNotification($warningDetail, $removeMassage, $removedIds);


                }
            }
            return redirect()->route('admin.warning.index')
                ->with('success', __('message.update_warning'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_warning');
        try{
            DB::beginTransaction();
            $this->warningService->deleteWarning($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_warning'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    private function sendNotification($warningDetail,$message, $userIds)
    {
        $notificationData['title'] = __('message.warning_notification');
        $notificationData['type'] = 'warning';
        $notificationData['user_id'] = $userIds;
        $notificationData['description'] = $message;
        $notificationData['notification_for_id'] = $warningDetail->id;
        $notification = $this->notificationService->store($notificationData);
        if($notification){
            SMPushHelper::sendWarningNotification('Warning Notification', $message, $userIds);
        }

    }


}
