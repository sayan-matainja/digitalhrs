<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Requests\Leave\LeaveApprovalRequest;
use App\Services\Leave\LeaveApprovalService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveApprovalController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.leaveApproval.';

    public function __construct(
        protected LeaveApprovalService $approvalService, protected DepartmentRepository $departmentRepository,
        protected RoleRepository $roleRepository, protected UserRepository $userRepository, protected LeaveTypeRepository $leaveTypeRepository, protected CompanyRepository $companyRepository
    ){}

    /**
     * Display a listing of the resource.
     *
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_leave_approval');
        try{
            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'leave_type_id' => $request->leave_type_id ?? null,
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }
            $select = ['*'];
            $with = ['leaveType:id,name'];
            $leaveApprovals = $this->approvalService->getAllLeaveApprovalPaginated($filterParameters,$select,$with);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view.'index', compact('leaveApprovals','companyDetail','filterParameters'));
        }catch(Exception $exception){
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
        $this->authorize('create_leave_approval');

        try{
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);

            $permissionKey = 'update_leave_request';
            $roles = $this->roleRepository->getAllActiveRolesByPermission($permissionKey);

            return view($this->view.'create', compact('companyDetail','roles'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param LeaveApprovalRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(LeaveApprovalRequest $request)
    {
        $this->authorize('create_leave_approval');
        try{
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->approvalService->saveLeaveApprovalDetail($validatedData);
            DB::commit();
            return redirect()->route('admin.leave-approval.index')->with('success',__('message.add_leave_approval') );
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
        $this->authorize('show_leave_approval');

        try{
            $select = ['*'];
            $with = ['leaveType:id,name','approvalDepartment.department:id,dept_name','approvalProcess.user.role:id,name'];
            $leaveApprovalDetail = $this->approvalService->findLeaveApprovalById($id, $select,$with);

            return view($this->view.'show', compact('leaveApprovalDetail'));
        }catch(Exception $exception){
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
        $this->authorize('update_leave_approval');
        try{
            $departmentId = [];
            $select = ['*'];
            $with = ['approvalDepartment.department:id,dept_name','approvalRole.role:id,name','approvalProcess.user:id,name'];
            $leaveApprovalDetail = $this->approvalService->findLeaveApprovalById($id, $select,$with);
            $permissionKey = 'update_leave_request';
            $roles = $this->roleRepository->getAllActiveRolesByPermission($permissionKey);

            foreach($leaveApprovalDetail->approvalDepartment as $key => $value){

                $departmentId[] = $value->department->id;
            }

            foreach ($leaveApprovalDetail->approvalProcess as $process) {
                $process->users = $this->userRepository->getUserByRole($process->role_id,['name','id']);
            }

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view.'edit', compact('leaveApprovalDetail','companyDetail','departmentId','roles'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param LeaveApprovalRequest $request
     * @param int $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(LeaveApprovalRequest $request, $id)
    {
        $this->authorize('update_leave_approval');
        try{
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->approvalService->updateLeaveApprovalDetail($id,$validatedData);
            DB::commit();
            return redirect()->route('admin.leave-approval.index')
                ->with('success', __('message.update_leave_approval'));
        }catch(Exception $exception){
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
        $this->authorize('delete_leave_approval');
        try{
            DB::beginTransaction();
            $this->approvalService->deleteLeaveApproval($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_leave_approval'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }


    public function toggleStatus($id): RedirectResponse
    {
        try {
            $this->authorize('update_leave_approval');
            $this->approvalService->changeStatus($id);
            return redirect()
                ->back()
                ->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function getEmployeesByRole(Request $request): JsonResponse
    {
        try {
            $roleId = $request->input('role_id');
            $select = ['name', 'id'];

            $employees = $this->userRepository->getUserByRole($roleId, $select);

            return response()->json([
                'success' => true,
                'data' => $employees
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * @param $branchId
     * @return JsonResponse
     */
    public function getBranchLeaveData($branchId)
    {
        try {

            $types = $this->leaveTypeRepository->getAllActiveLeaveTypeByBranch($branchId, ['id','name']);
            $departments = $this->departmentRepository->getAllActiveDepartmentsByBranchId($branchId,[], ['id','dept_name']);

            return response()->json([
                'types' => $types,
                'departments' => $departments,
            ]);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }

    }
}
