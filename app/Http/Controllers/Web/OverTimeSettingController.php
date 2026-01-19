<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\OverTimeSetting;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\OverTimeSettingRepository;
use App\Repositories\UserRepository;
use App\Requests\Payroll\OverTime\OverTimeRequest;
use App\Services\Payroll\OverTimeSettingService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OverTimeSettingController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.payrollSetting.overtime.';

    public function __construct(protected OverTimeSettingService $otService, protected UserRepository $userRepository,
                                protected CompanyRepository $companyRepository , protected DepartmentRepository $departmentRepository)
    {}


    public function index(): Factory|View|RedirectResponse|Application
    {
        $this->authorize('overtime_setting');
        try {

            $select = ['*'];
            $overTimeData = $this->otService->getAllOTList($select);

            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            return view($this->view . 'index', compact('overTimeData','currency'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(): View|Factory|RedirectResponse|Application
    {
        $this->authorize('overtime_setting');
        try {

            $withCompany = ['branches:id,name'];
            $selectCompany = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($selectCompany, $withCompany);
            return view($this->view . 'create',compact('companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @param OverTimeRequest $request
     * @return View|Factory|RedirectResponse|Application
     */
    public function store(OverTimeRequest $request): View|Factory|RedirectResponse|Application
    {
        $this->authorize('overtime_setting');
        try {

            $validatedData = $request->validated();
            $validatedData['is_active'] = 1;
            DB::beginTransaction();
            $this->otService->store($validatedData);
            DB::commit();
            return redirect()->route('admin.overtime.index')->with('success', __('message.overtime_add'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function edit($id): View|Factory|RedirectResponse|Application
    {
        $this->authorize('overtime_setting');
        try {

            $with = ['otEmployees','otDepartments'];
            $overtime = $this->otService->findOTById($id, $with);

            $employeeIds = $overtime?->otEmployees?->pluck('employee_id')->toArray() ?? [];
            $departmentIds = $overtime?->otDepartments?->pluck('department_id')->toArray() ?? [];
            $withCompany = ['branches:id,name'];
            $selectCompany = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($selectCompany, $withCompany);
            $departments = $this->departmentRepository->getAllActiveDepartmentsByBranchId($overtime->branch_id,[],['id','dept_name']);
            $employees = $this->userRepository->getActiveEmployeesByDepartments($departmentIds,['id','name']);
            return view($this->view . 'edit', compact('overtime','employeeIds','companyDetail','departmentIds','departments','employees'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function update(OverTimeRequest $request, $id): RedirectResponse
    {
        $this->authorize('overtime_setting');
        try {

            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->otService->updateOverTime($id, $validatedData);
            DB::commit();
            return redirect()->route('admin.overtime.index')->with('success', __('message.overtime_update'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete($id): RedirectResponse
    {
        $this->authorize('overtime_setting');
        try {

            DB::beginTransaction();
            $this->otService->deleteOTSetting($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.overtime_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleOTStatus($id): RedirectResponse
    {
        $this->authorize('overtime_setting');
        try {

            DB::beginTransaction();
            $this->otService->changeOTStatus($id);
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
