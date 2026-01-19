<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\Tada\TadaRequest;
use App\Requests\Tada\TadaUpdateStatusRequest;
use App\Services\Tada\TadaService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TadaController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.tada.';


    public function __construct(protected TadaService $tadaService, protected UserRepository $userRepo,
                                protected CompanyRepository $companyRepository,
                                protected DepartmentRepository $departmentRepository)
    {}

    public function index(Request $request)
    {
        $this->authorize('view_tada_list');
        try {
            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'status' => $request->status ?? null
            ];
            $select = ['*'];
            $with = ['employeeDetail:id,name'];
            $tadaLists = $this->tadaService->getAllTadaDetailPaginated($filterParameters,$select,$with);
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'index',compact('tadaLists','filterParameters','currency','companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create_tada');
        try {
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            $attachments = [];
            return view($this->view . 'create',compact('companyDetail','attachments'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(TadaRequest $request)
    {
        $this->authorize('create_tada');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
                $this->tadaService->store($validatedData);
            DB::commit();
            return redirect()
                ->route('admin.tadas.index')
                ->with('success', __('message.add_tada'));
        }catch(Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $this->authorize('show_tada_detail');
        try {
            $select= ['*'];
            $with = ['employeeDetail:id,name','attachments','verifiedBy:id,name'];
            $tadaDetail = $this->tadaService->findTadaDetailById($id,$with,$select);
            $attachments = $tadaDetail->attachments;
            return view($this->view . 'show',compact('tadaDetail','attachments'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function edit($id)
    {
        $this->authorize('edit_tada');
        try {
            $select= ['*'];
            $with = ['employeeDetail:id,name','attachments'];
            $tadaDetail = $this->tadaService->findTadaDetailById($id,$with,$select);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);

            // Fetch users by selected departments
            $filteredDepartment = isset($tadaDetail->branch_id)
                ? $this->departmentRepository->getAllActiveDepartmentsByBranchId($tadaDetail->branch_id, [], ['id', 'dept_name'])
                : [];

            $select = ['name', 'id'];
            $filteredUsers = isset($tadaDetail->department_id)
                ? $this->userRepo->getActiveEmployeeOfDepartment($tadaDetail->department_id, $select)
                : [];


            $attachments = $tadaDetail->attachments;
            return view($this->view .'edit',compact('tadaDetail','companyDetail','attachments','filteredUsers','filteredDepartment'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(TadaRequest $request, $id)
    {
        $this->authorize('edit_tada');
        try {
            $validatedData = $request->validated();
            $with=['attachments'];
            $tadaDetail = $this->tadaService->findTadaDetailById($id,$with);
            DB::beginTransaction();
            $this->tadaService->update($tadaDetail,$validatedData);
            DB::commit();
            return redirect()->route('admin.tadas.index')
                ->with('success', __('message.update_tada'));
        }catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_tada');
        try {
            DB::beginTransaction();
                $this->tadaService->delete($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_tada'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleTadaIsActive($id)
    {
        $this->authorize('edit_tada');
        try {
            DB::beginTransaction();
            $this->tadaService->toggleStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.tada_settlement_change'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function changeTadaStatus(TadaUpdateStatusRequest $request,$id)
    {
        $this->authorize('edit_tada');
        try {
            $validatedData = $request->validated();
            $tadaDetail = $this->tadaService->findTadaDetailById($id);
            $this->tadaService->changeTadaStatus($tadaDetail,$validatedData);
            return redirect()->back()->with('success', __('message.tada_status_change'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}
