<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\Payroll\Bonus\BonusRequest;
use App\Services\Payroll\BonusService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BonusController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.payrollSetting.bonus.';


    public function __construct(public BonusService $bonusService, protected UserRepository $userRepository, protected CompanyRepository $companyRepository,
    protected DepartmentRepository $departmentRepository)
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function index()
    {

        $this->authorize('bonus');
        try {


            $select = ['*'];
            $with = ['bonusEmployee.employee:id,name'];
            $bonusList = $this->bonusService->getAllBonusList($select, $with);
            $months = AppHelper::getMonthsList();
            $employees = $this->userRepository->getEmployeeForBonus(['id','name']);
            $withCompany = ['branches:id,name'];
            $selectCompany = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($selectCompany, $withCompany);
            return view($this->view . 'index', compact('bonusList','months','employees','companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

//    public function create()
//    {
//        $this->authorize('bonus');
//        try {
//
//            $months = AppHelper::getMonthsList();
//            return view($this->view . 'create', compact('months'));
//        } catch (Exception $exception) {
//            return redirect()->back()->with('danger', $exception->getMessage());
//        }
//    }

    public function store(BonusRequest $request)
    {
        $this->authorize('bonus');
        try {

            $validatedData = $request->validated();
            $validatedData['apply_for_all'] = $validatedData['apply_for_all'] ?? 0;
            DB::beginTransaction();
            $this->bonusService->store($validatedData);
            DB::commit();
            return response()->json(['success' => true, 'message' => __('message.add_bonus')]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $this->authorize('bonus');
        try {
            $months = AppHelper::getMonthsList();
            $bonusDetail = $this->bonusService->findBonusById($id,['*'],['bonusEmployee','bonusDepartment']);

            $employeeIds = [];
            foreach ($bonusDetail->bonusEmployee as $value) {
                $employeeIds[] = $value->employee_id;
            }
            $departmentIds = [];
            foreach ($bonusDetail->bonusDepartment as $value) {
                $departmentIds[] = $value->department_id;
            }
            $departments = $this->departmentRepository->getAllActiveDepartmentsByBranchId($bonusDetail->branch_id,[],['id','dept_name']);
            $employees = $this->userRepository->getEmployeeByDepartmentForBonus($departmentIds, ['id','name']);

            return response()->json(['bonusDetail' => $bonusDetail, 'months' => $months, 'departments' => $departments, 'employees' => $employees, 'departmentIds' => $departmentIds, 'employeeIds' => $employeeIds]);
        } catch (Exception $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 404);
        }
    }


    public function update(BonusRequest $request, $id)
    {
        $this->authorize('bonus');
        try {

            $validatedData = $request->validated();
            $validatedData['apply_for_all'] = $validatedData['apply_for_all'] ?? 0;
            DB::beginTransaction();
            $this->bonusService->updateDetail($id, $validatedData);
            DB::commit();
            return response()->json(['success' => true, 'message' => __('message.update_bonus')]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function delete($id)
    {
        $this->authorize('bonus');
        try {

            $bonusDetail = $this->bonusService->findBonusById($id);
            DB::beginTransaction();
            $this->bonusService->deleteBonusDetail($bonusDetail);
            DB::commit();
            return redirect()
                ->back()
                ->with('success', __('message.delete_bonus'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleBonusStatus($id)
    {
        $this->authorize('bonus');
        try {

            $bonusDetail = $this->bonusService->findBonusById($id);
            DB::beginTransaction();
            $this->bonusService->changeBonusStatus($bonusDetail);
            DB::commit();
            return redirect()
                ->back()
                ->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function getUserDepartmentData(Request $request){
        try {
            $departmentIds = $request->input('department_ids');
            $employees = $this->userRepository->getEmployeeByDepartmentForBonus($departmentIds, ['id','name']);

            return response()->json([
                'status'=>200,
                'employees' => $employees,
            ]);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }
}
