<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\Department\DepartmentStoreRequest;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.department.';

    private DepartmentRepository $departmentRepo;
    private UserRepository $userRepo;
    private BranchRepository $branchRepo;

    public function __construct(DepartmentRepository $departmentRepo,
                                UserRepository $userRepo,
                                BranchRepository $branchRepo)
    {
        $this->departmentRepo = $departmentRepo;
        $this->userRepo = $userRepo;
        $this->branchRepo = $branchRepo;
    }

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_department');
        try {
            $filterParameters = [
                'company_id' => AppHelper::getAuthUserCompanyId(),
                'name' =>  $request->name ?? null,
                'branch' =>  $request->branch ?? null,
                'per_page' => $request->per_page ?? Department::RECORDS_PER_PAGE,
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch'] = auth()->user()->branch_id;
            }

            $selectBranch = ['id','name'];
            $with = ['branch:id,name', 'departmentHead:id,name'];
            $branch = $this->branchRepo->getLoggedInUserCompanyBranches($filterParameters['company_id'],$selectBranch);
            $departments = $this->departmentRepo->getAllPaginatedDepartments($filterParameters,$with);
            return view($this->view . 'index', compact('departments','filterParameters','branch'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create_department');
        try {
            $select = ['name','id'];
            $branches = $this->branchRepo->getLoggedInUserCompanyBranches(AppHelper::getAuthUserCompanyId(),['id','name']);

            return view($this->view . 'create',
                compact('branches')
            );
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function getAllDepartmentsByBranchId($branchId): JsonResponse|RedirectResponse
    {
        try {

            $with = [];
            $select = ['dept_name', 'id'];
            $departments = $this->departmentRepo->getAllActiveDepartmentsByBranchId($branchId,$with,$select);
            return response()->json([
                'data' => $departments
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function store(DepartmentStoreRequest $request)
    {
        $this->authorize('create_department');
        try {
            $validatedData = $request->validated();
            if(isset($validatedData['dept_head_id'])){
                $this->checkDepartmentHead($validatedData['dept_head_id']);
            }
            DB::beginTransaction();
            $this->departmentRepo->store($validatedData);
            DB::commit();
            return redirect()
                ->route('admin.departments.index')
                ->with('success', __('message.add_department'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('admin.departments.index')
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $this->authorize('edit_department');
        try {
            $departmentsDetail = $this->departmentRepo->findDepartmentById($id);

            $branches = $this->branchRepo->getLoggedInUserCompanyBranches(AppHelper::getAuthUserCompanyId(),['id','name']);

            $selectUser = ['name', 'id'];
            $filteredUsers = isset($departmentsDetail->branch_id)
                ? $this->userRepo->getActiveEmployeeOfBranch($departmentsDetail->branch_id, $selectUser)
                : [];
            return view($this->view . 'edit',
                compact('branches', 'filteredUsers', 'departmentsDetail')
            );
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(DepartmentStoreRequest $request, $id)
    {
        $this->authorize('edit_department');
        try {
            $validatedData = $request->validated();
            $departmentDetail = $this->departmentRepo->findDepartmentById($id);
            if (!$departmentDetail) {
                throw new Exception(__('message.update_department'), 404);
            }
            if(isset($validatedData['dept_head_id'])){
                $this->checkDepartmentHead($validatedData['dept_head_id'],$id);
            }

            DB::beginTransaction();
            $department = $this->departmentRepo->update($departmentDetail, $validatedData);
            DB::commit();
            return redirect()
                ->route('admin.departments.index')
                ->with('success', __('message.update_department'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }

    }

    public function toggleStatus($id)
    {
        $this->authorize('edit_department');
        try {
            DB::beginTransaction();
            $this->departmentRepo->toggleStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_department');
        try {
            $select = ['*'];
            $with = ['posts'];
            $departmentDetail = $this->departmentRepo->findDepartmentById($id,$select,$with);
            if (!$departmentDetail) {
                throw new Exception(__('message.department_not_found'), 404);
            }
            if(count($departmentDetail->posts) > 0){
                throw new Exception(__('message.delete_department_warning'),403);
            }

            DB::beginTransaction();
            $this->departmentRepo->delete($departmentDetail);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_department'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function checkDepartmentHead($userId, $departmentId=0)
    {
        $statusCheck =  $this->departmentRepo->checkDepartmentHead($userId, $departmentId);

        if($statusCheck){
            throw new Exception(__('index.department_head_error'),404);
        }
    }

}
