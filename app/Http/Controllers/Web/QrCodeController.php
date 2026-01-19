<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Services\Qr\QrCodeService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class QrCodeController extends Controller
{
    use CustomAuthorizesRequests;

    private $view = 'admin.qr.';

    public function __construct(public QrCodeService           $qrCodeService,
                                protected BranchRepository     $branchRepository,
                                protected DepartmentRepository $departmentRepository,
                                protected CompanyRepository    $companyRepo)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|RedirectResponse
     * @throws AuthorizationException
     */
    public function index(Request $request): View|Factory|RedirectResponse|Application
    {
        $this->authorize('list_qr');

        try {

            $filterData = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
            ];

            if (!auth('admin')->check() && auth()->check()) {
                $filterData['branch_id'] = auth()->user()->branch_id;
            }
            $qrData = $this->qrCodeService->getAllQr($filterData);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            return view($this->view . 'index', compact('qrData', 'filterData', 'companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|RedirectResponse|Response
     */
    public function create(): View|Factory|Response|RedirectResponse|Application
    {
        try {
            $this->authorize('create_qr');

            $companyId = AppHelper::getAuthUserCompanyId();
            $selectBranch = ['id', 'name'];
            $branches = $this->branchRepository->getLoggedInUserCompanyBranches($companyId, $selectBranch);
            return view($this->view . 'create', compact('branches'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function store(Request $request): Response|RedirectResponse
    {
        try {
            $this->authorize('create_qr');

            $validatedData = $request->all();
            if (!auth('admin')->check() && auth()->check()) {
                $validatedData['branch_id'] = auth()->user()->branch_id;
            }
            DB::beginTransaction();
            $this->qrCodeService->saveQrDetail($validatedData);
            DB::commit();
            return redirect()->route('admin.qr.index')->with('success', __('message.qr_add'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Application|Factory|View|RedirectResponse|Response
     */
    public function edit(int $id): View|Factory|Response|RedirectResponse|Application
    {
        try {
            $this->authorize('edit_qr');

            $qrData = $this->qrCodeService->findQrDetailById($id);

            $companyId = AppHelper::getAuthUserCompanyId();

            $selectBranch = ['id', 'name'];

            $branches = $this->branchRepository->getLoggedInUserCompanyBranches($companyId, $selectBranch);

//            // Fetch users by selected departments
//            $filteredDepartment = isset($qrData->branch_id)
//                ? $this->departmentRepository->getAllActiveDepartmentsByBranchId($qrData->branch_id, [], ['id', 'dept_name'])
//                : [];

            return view($this->view . 'edit', compact('qrData', 'branches'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|Response
     */
    public function update(Request $request, int $id): Response|RedirectResponse
    {
        try {
            $this->authorize('edit_qr');
            DB::beginTransaction();
            $this->qrCodeService->updateQrDetail($request->all(), $id);
            DB::commit();
            return redirect()->route('admin.qr.index')->with('success', __('message.qr_update'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return RedirectResponse|Response
     */
    public function delete(int $id): Response|RedirectResponse
    {
        try {
            $this->authorize('delete_qr');

            DB::beginTransaction();
            $this->qrCodeService->deleteQrDetail($id);
            DB::commit();
            return redirect()->route('admin.qr.index')->with('success', __('message.qr_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @param int $id
     * @return Application|Factory|View|RedirectResponse
     */
    public function print(int $id): View|Factory|RedirectResponse|Application
    {
        try {
            $qrData = $this->qrCodeService->findQrDetailById($id);
            return view($this->view . 'print', compact('qrData'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
}
