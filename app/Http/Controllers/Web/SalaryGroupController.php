<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EmployeePayslip;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\Payroll\SalaryGroup\SalaryGroupRequest;
use App\Services\Payroll\SalaryComponentService;
use App\Services\Payroll\SalaryGroupService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SalaryGroupController extends Controller
{

    use CustomAuthorizesRequests;
    private $view = 'admin.payrollSetting.salaryGroup.';

    public function __construct(
        public SalaryGroupService     $salaryGroupService,
        public SalaryComponentService $salaryComponentService,
        public UserRepository $userRepo,
        public CompanyRepository $companyRepository,
        public DepartmentRepository $departmentRepository,
    )
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function index(): Factory|View|RedirectResponse|Application
    {
        $this->authorize('salary_group');
        try {
            $select = ['*'];
            $with = ['salaryComponents:id,name', 'groupEmployees', 'groupDepartment'];
            $salaryGroupLists = $this->salaryGroupService->getAllSalaryGroupDetailList($select, $with);

            // Transform salaryGroupLists to include department_ids and branch_id
            $salaryGroupLists = $salaryGroupLists->map(function ($group) {
                $group->department_ids = $group->groupDepartment->pluck('department_id')->toArray();
                $group->branch_id = $group->groupDepartment->first()->department->branch_id ?? null;
                return $group;
            });

            $salaryComponents = $this->salaryComponentService->pluckAllActiveSalaryComponent();
            $employees = $this->userRepo->pluckIdAndNameOfAllVerifiedEmployee();
            $withCompany = ['branches:id,name'];
            $selectCompany = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($selectCompany, $withCompany);

            return view($this->view . 'index', compact('salaryGroupLists', 'salaryComponents', 'employees', 'companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create(): Factory|View|RedirectResponse|Application
    {
        $this->authorize('salary_group');
        try {

            $salaryComponents = $this->salaryComponentService->pluckAllActiveSalaryComponent();
            $employees = $this->userRepo->pluckIdAndNameOfAllVerifiedEmployee();
            return view($this->view . 'create', compact('salaryComponents','employees'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(SalaryGroupRequest $request)
    {
        $this->authorize('salary_group');
        try {
            $validatedData = $request->validated();

            DB::beginTransaction();
            $this->salaryGroupService->store($validatedData);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => __('message.salary_group_add')
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function edit($id)
    {
        $this->authorize('salary_group');
        try {

            $groupSelect = ['*'];
            $with = ['salaryComponents:id,name','groupEmployees:salary_group_id,employee_id','groupDepartment'];
            $salaryGroupDetail = $this->salaryGroupService->findOrFailSalaryGroupDetailById($id,$groupSelect,$with);
            $salaryComponents = $this->salaryComponentService->pluckAllActiveSalaryComponent();

            $componentIds = $salaryGroupDetail?->salaryComponents?->pluck('id')->toArray() ?? [];
            $employeeIds = $salaryGroupDetail?->groupEmployees?->pluck('employee_id')->toArray() ?? [];
            $departmentIds = $salaryGroupDetail?->groupDepartment?->pluck('department_id')->toArray() ?? [];

            $departments = $this->departmentRepository->getAllActiveDepartmentsByBranchId($salaryGroupDetail->branch_id,[],['id','dept_name']);
            $employees = $this->userRepo->getEmployeeByDepartmentForBonus($departmentIds, ['id','name']);

            return response()->json(['salaryGroupDetail' => $salaryGroupDetail, 'salaryComponents' => $salaryComponents, 'departments' => $departments, 'employees' => $employees, 'departmentIds' => $departmentIds, 'employeeIds' => $employeeIds, 'componentIds' => $componentIds]);

        } catch (Exception $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 404);
        }
    }

    public function update(SalaryGroupRequest $request, $id)
    {
        $this->authorize('salary_group');
        try {
            $select = ['*'];
            $validatedData = $request->validated();
            $salaryGroupDetail = $this->salaryGroupService->findOrFailSalaryGroupDetailById($id, $select);
            DB::beginTransaction();
            $this->salaryGroupService->updateDetail($salaryGroupDetail, $validatedData);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => __('message.salary_group_update')
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function deleteSalaryGroup($id): RedirectResponse
    {
        $this->authorize('salary_group');
        try {

            $select = ['*'];
//            $payrollCount = EmployeePayslip::where('salary_group_id', $id)->count();
//            if($payrollCount > 0){
//                return redirect()
//                    ->back()
//                    ->with('danger', __('message.salary_group_delete_error'));
//            }
            $salaryGroupDetail = $this->salaryGroupService->findOrFailSalaryGroupDetailById($id, $select);
            DB::beginTransaction();

            $this->salaryGroupService->deleteSalaryGroupDetail($salaryGroupDetail);
            DB::commit();
            return redirect()
                ->back()
                ->with('success', __('message.salary_group_delete'));
        } catch (Exception $exception) {
            DB::rollBack();

            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleSalaryGroupStatus($id): RedirectResponse
    {
        $this->authorize('salary_group');
        try {

            $select = ['*'];
            $salaryGroupDetail = $this->salaryGroupService->findOrFailSalaryGroupDetailById($id, $select);
            DB::beginTransaction();
            $this->salaryGroupService->changeSalaryGroupStatus($salaryGroupDetail);
            DB::commit();
            return redirect()
                ->back()
                ->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

}
