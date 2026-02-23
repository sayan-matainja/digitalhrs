<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Requests\Payroll\SalaryComponent\SalaryComponentRequest;
use App\Services\Payroll\SalaryComponentService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use App\Models\SalaryComponent; //added

class SalaryComponentController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.payrollSetting.salaryComponent.';

    public function __construct(public SalaryComponentService $salaryComponentService)
    {
    }

    public function index()
    {
        $this->authorize('salary_component');
        try {

            $select = ['*'];
            $salaryComponentLists = $this->salaryComponentService->getAllSalaryComponentList($select);
            return view($this->view . 'index', compact('salaryComponentLists'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('salary_component');
        try {

            return view($this->view . 'create');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(SalaryComponentRequest $request)
    {
        $this->authorize('salary_component');
        try {

            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->salaryComponentService->store($validatedData);
            DB::commit();
            return redirect()
                ->route('admin.salary-components.index')
                ->with('success', __('message.salary_component_add'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $this->authorize('salary_component');
        try {

            $select = ['*'];
            $salaryComponentDetail = $this->salaryComponentService->findSalaryComponentById($id, $select);
            return view($this->view . 'edit', compact('salaryComponentDetail'));
        } catch (Exception $exception) {
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function update(SalaryComponentRequest $request, $id)
    {
        $this->authorize('salary_component');
        try {

            $select = ['*'];
            $salaryComponentDetail = $this->salaryComponentService->findSalaryComponentById($id, $select);
            $validatedData = $request->validated();

            $validatedData['apply_for_all'] = $validatedData['apply_for_all'] ?? false;
            DB::beginTransaction();
            $this->salaryComponentService->updateDetail($salaryComponentDetail, $validatedData);
            DB::commit();;
            return redirect()
                ->route('admin.salary-components.index')
                ->with('success', __('message.salary_component_update'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function delete($id)
    {
        $this->authorize('salary_component');
        try {

//            $exists = $this->salaryComponentService->checkComponentUse($id);
//            if($exists){
//                return redirect()
//                    ->back()
//                    ->with('danger', __('message.salary_component_delete_error'));
//            }
            DB::beginTransaction();
            $this->salaryComponentService->deleteSalaryComponentDetail($id);
            DB::commit();
            return redirect()
                ->back()
                ->with('success', __('message.salary_component_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleSalaryComponentStatus($id)
    {
        $this->authorize('salary_component');

        try {
            $select = ['*'];
//            $exists = $this->salaryComponentService->checkComponentUse($id);
            $salaryComponentDetail = $this->salaryComponentService->findSalaryComponentById($id, $select);
//            if($exists && $salaryComponentDetail->status == 1){
//                return redirect()
//                    ->back()
//                    ->with('danger', __('message.salary_component_status_change_error'));
//            }
            DB::beginTransaction();
            $this->salaryComponentService->changeSalaryComponentStatus($salaryComponentDetail);
            DB::commit();
            return redirect()
                ->back()
                ->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    //added
    /**
     * Toggle taxable status of salary component
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleTaxableStatus($id)
    {
        try {
            $salaryComponent = SalaryComponent::findOrFail($id);
            $salaryComponent->taxable = $salaryComponent->taxable == 1 ? 0 : 1;
            $salaryComponent->save();

            return redirect()->back()->with('success', 'Taxable status changed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error changing taxable status: ' . $e->getMessage());
        }
    }
}
