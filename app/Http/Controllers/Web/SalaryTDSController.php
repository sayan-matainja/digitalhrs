<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Helpers\PayrollHelper;
use App\Http\Controllers\Controller;
use App\Requests\Payroll\SalaryTDS\SalaryTDSUpdateRequest;
use App\Requests\Payroll\SalaryTDS\SalaryTDSStoreRequest;
use App\Services\Payroll\SalaryTDSService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;

class SalaryTDSController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.payrollSetting.salaryTDS.';

    public function __construct(public SalaryTDSService $salaryTDSService)
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function index()
    {

        $this->authorize('salary_tds');
        try {
            $select = ['*'];
            $salaryTDSList = $this->salaryTDSService->getAllSalaryTDSListGroupByMaritalStatus($select);
            $singleSalaryTDS = $salaryTDSList->get('single', collect());
            $marriedSalaryTDS = $salaryTDSList->get('married', collect());

            return view($this->view . 'index', compact('singleSalaryTDS','marriedSalaryTDS'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function create()
    {
        $this->authorize('salary_tds');
        try {

            return view($this->view . 'create');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function store(SalaryTDSStoreRequest $request)
    {
        $this->authorize('salary_tds');
        try {

            $validatedData = $request->validated();
            $this->salaryTDSService->store($validatedData);
            return AppHelper::sendSuccessResponse(__('message.salary_tds_add'));
        } catch (Exception $e) {
           return AppHelper::sendErrorResponse($e->getMessage(),$e->getCode());
        }
    }


    public function edit($id)
    {
        $this->authorize('salary_tds');
        try {

            $salaryTDSDetail = $this->salaryTDSService->findSalaryTDSById($id);
            return view($this->view . 'edit',compact('salaryTDSDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function update(SalaryTDSUpdateRequest $request, $id)
    {
        try{

            $validatedData = $request->validated();
            $salaryTDSDetail = $this->salaryTDSService->findSalaryTDSById($id);
            $this->salaryTDSService->updateDetail($salaryTDSDetail,$validatedData);
            return redirect()
                ->route('admin.salary-tds.index')
                ->with('success', __('message.salary_tds_update'));
        }catch(Exception $exception){
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }


    public function deleteSalaryTDS($id)
    {
        $this->authorize('salary_tds');
        try {

            $select = ['*'];
            $salaryTDSDetail = $this->salaryTDSService->findSalaryTDSById($id, $select);
            $this->salaryTDSService->deleteSalaryTDSDetail($salaryTDSDetail);
            return redirect()
                ->back()
                ->with('success', __('message.salary_tds_delete'));
        } catch (Exception $exception) {
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function toggleSalaryTDSStatus($id)
    {
        $this->authorize('salary_tds');
        try {

            $select = ['*'];
            $salaryTDSDetail = $this->salaryTDSService->findSalaryTDSById($id, $select);
            $this->salaryTDSService->changeSalaryTDSStatus($salaryTDSDetail);
            return redirect()
                ->back()
                ->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
}
