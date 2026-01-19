<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Requests\Company\CompanyRequest;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.company.';

    private CompanyRepository $companyRepo;

    public function __construct(CompanyRepository $companyRepo)
    {
        $this->companyRepo = $companyRepo;
    }

    /**
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('view_company');
        try {
            $companyDetail = $this->companyRepo->getCompanyDetail();
            return view($this->view . 'index', compact('companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function store(CompanyRequest $request)
    {
        $this->authorize('create_company');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->companyRepo->store($validatedData);
            DB::commit();
            return redirect()->route('admin.company.index')->with('success', __('message.add_company'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('admin.company.index')
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }


    public function update(CompanyRequest $request, $id)
    {

        $this->authorize('edit_company');
        try {
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'),400);
            }
            $validatedData = $request->validated();
            $validatedData['weekend'] = $validatedData['weekend'] ?? [];
            $validatedData['count_holiday_weekend'] = $validatedData['count_holiday_weekend'] ?? false;
            $companyDetail = $this->companyRepo->findOrFailCompanyDetailById($id);
            if (!$companyDetail) {
                throw new Exception('Company Detail Not Found', 404);
            }
            DB::beginTransaction();
            $this->companyRepo->update($companyDetail, $validatedData);
            DB::commit();
            return redirect()->route('admin.company.index')
                ->with('success', __('message.update_company'));

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('admin.company.index')
                ->with('danger', $e->getMessage())
                ->withInput();

        }
    }
}
