<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Requests\Payroll\PF\PFRequest;
use App\Services\FiscalYear\FiscalYearService;
use App\Services\Payroll\PFService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PFController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.payrollSetting.pf.';


    public function __construct(protected PFService $pfService, protected FiscalYearService $fiscalYearService)
    {}

    /**
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('pf');
        try {
            $applicableDate = '';
            $pfDetail = $this->pfService->getPF();

            $fiscalYear = $this->fiscalYearService->getActiveFiscalYear();

            if(AppHelper::ifDateInBsEnabled() && isset($pfDetail->applicable_date)){
                $pfDetail->applicable_date = AppHelper::dateInYmdFormatEngToNep($pfDetail->applicable_date) ??  '';
            }else{
                $applicableDate = isset($fiscalYear) ? AppHelper::dateInYmdFormatEngToNep($fiscalYear->start_date) : '';
            }

            return view($this->view . 'index', compact('pfDetail','applicableDate'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function store(PFRequest $request)
    {
        $this->authorize('pf');
        try {
            $validatedData = $request->all();
            DB::beginTransaction();
            $this->pfService->storePF($validatedData);
            DB::commit();
            return redirect()->route('admin.pf.index')->with('success', __('message.pf_add'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('admin.pf.index')
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }


    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(PFRequest $request, $id)
    {

        $this->authorize('pf');
        try {

            $validatedData = $request->all();
            $validatedData['enable_tax_exemption'] = $validatedData['enable_tax_exemption'] ?? false;
            DB::beginTransaction();
            $this->pfService->updatePF($id, $validatedData);
            DB::commit();
            return redirect()->route('admin.pf.index')
                ->with('success', __('message.pf_update'));

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('admin.pf.index')
                ->with('danger', $e->getMessage())
                ->withInput();

        }
    }
}
