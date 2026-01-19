<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Services\Nfc\NfcService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NFCController extends Controller
{

    use CustomAuthorizesRequests;
    private $view = 'admin.nfc.';

    public function __construct(Public NfcService $nfcService, protected CompanyRepository    $companyRepo)
    {}

    /**
     * @return Application|Factory|View|RedirectResponse
     * @throws AuthorizationException
     */
    public function index(Request $request): View|Factory|RedirectResponse|Application
    {
        $this->authorize('list_nfc');

        try {
            $filterData = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
            ];

            if (!auth('admin')->check() && auth()->check()) {
                $filterData['branch_id'] = auth()->user()->branch_id;
            }
            $nfcData = $this->nfcService->getAllNfc($filterData);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            return view($this->view . 'index', compact('nfcData','filterData','companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @param $id
     * @return RedirectResponse
     *
     */
    public function delete($id): RedirectResponse
    {
        try {
            $this->authorize('delete_nfc');

            DB::beginTransaction();
            $this->nfcService->deleteNfcDetail($id);
            DB::commit();
            return redirect()->route('admin.nfc.index')->with('success', __('nfc.nfc_deleted'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
}
