<?php

namespace App\Http\Controllers\Web;

use App\Exports\UserExport;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\EmployeeCardUpload;
use App\Models\IdCardSetting;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\EmployeeLeaveTypeRepository;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\OfficeTimeRepository;
use App\Repositories\PostRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserAccountRepository;
use App\Repositories\UserRepository;
use App\Requests\User\ChangePasswordRequest;
use App\Requests\User\UserAccountRequest;
use App\Requests\User\UserCreateRequest;
use App\Requests\User\UserLeaveTypeRequest;
use App\Requests\User\UserUpdateRequest;
use App\Services\EmployeeCardSetting\EmployeeCardSettingService;
use App\Traits\CustomAuthorizesRequests;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;
use Picqer\Barcode\BarcodeGeneratorSVG;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Spatie\Browsershot\Browsershot;

class UserController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.employees.';


    public function __construct(protected UserRepository              $userRepo,
                                protected CompanyRepository           $companyRepo,
                                protected RoleRepository              $roleRepo,
                                protected OfficeTimeRepository        $officeTimeRepo,
                                protected UserAccountRepository       $accountRepo,
                                protected BranchRepository            $branchRepository,
                                protected LeaveTypeRepository         $leaveTypeRepository,
                                protected EmployeeLeaveTypeRepository $employeeLeaveTypeRepository,
                                protected PostRepository $postRepository,
                                protected EmployeeCardSettingService $cardTemplateService

    )
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_employee');
        try {


            $filterParameters = [
                'employee_name' => $request->employee_name ?? null,
                'email' => $request->email ?? null,
                'phone' => $request->phone ?? null,
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }



            $with = ['branch:id,name', 'company:id,name', 'post:id,post_name', 'department:id,dept_name', 'role:id,name','officeTime:id,shift,opening_time,closing_time','supervisor:id,name'];

            $select = ['users.*', 'branch_id', 'company_id', 'department_id', 'post_id', 'role_id'];
            $users = $this->userRepo->getAllUsers($filterParameters, $select, $with);

            $company = $this->companyRepo->getCompanyDetail(['id']);
            $branches = $this->branchRepository->getLoggedInUserCompanyBranches($company->id, ['id', 'name']);


            if ($request->input('action') == 'export') {
                $fileName = 'users.csv';
                return \Maatwebsite\Excel\Facades\Excel::download(new UserExport($users), $fileName);
            }

            return view($this->view . 'index', compact('users', 'filterParameters', 'branches'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create_employee');
        try {
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            $roles = $this->roleRepo->getAllActiveRoles();

            $employeeCode = AppHelper::getEmployeeCode();

            $bsEnabled = AppHelper::ifDateInBsEnabled();

            return view($this->view . 'create', compact('companyDetail', 'roles', 'employeeCode', 'bsEnabled'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function store(UserCreateRequest $request, UserAccountRequest $accountRequest, UserLeaveTypeRequest $leaveRequest)
    {
        $this->authorize('create_employee');
        try {
            $validatedData = $request->validated();

            // Create full name from surname, first_name, middle_name
            $nameParts = array_filter([
                $validatedData['surname'] ?? '',
                $validatedData['first_name'] ?? '',
                $validatedData['middle_name'] ?? ''
            ]);
            $validatedData['name'] = implode(' ', $nameParts);

            $accountValidatedData = $accountRequest->validated();
            $leaveTypeData = $leaveRequest->validated();

            $validatedData['password'] = bcrypt($validatedData['password']);
            $validatedData['is_active'] = 1;
            $validatedData['status'] = 'verified';
            $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
            $validatedData['allow_holiday_check_in'] = isset($validatedData['allow_holiday_check_in']) ? 1 : 0;

            DB::beginTransaction();
            $user = $this->userRepo->store($validatedData);
            $accountValidatedData['user_id'] = $user['id'];
            $this->accountRepo->store($accountValidatedData);

            if (!is_null($user['leave_allocated']) && isset($leaveTypeData['leave_type_id'])) {
                foreach ($leaveTypeData['leave_type_id'] as $key => $value) {
                    $input['days'] = $leaveTypeData['days'][$key] ?? 0;
                    $input['is_active'] = $leaveTypeData['is_active'][$key] ?? 0;
                    $input['employee_id'] = $user['id'];
                    $input['leave_type_id'] = $value;

                    $this->employeeLeaveTypeRepository->store($input);

                }
            }

            DB::commit();
            return redirect()
                ->route('admin.employees.index')
                ->with('success', __('message.add_user'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('show_detail_employee');
        try {
            $with = [
                'branch:id,name',
                'company:id,name',
                'post:id,post_name',
                'department:id,dept_name',
                'role:id,name',
                'accountDetail'
            ];
            $select = ['users.*', 'branch_id', 'company_id', 'department_id', 'post_id', 'role_id'];
            $userDetail = $this->userRepo->findUserDetailById($id, $select, $with);
            return view($this->view . 'show2', compact('userDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getFile());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit($id)
    {

        $this->authorize('edit_employee');
        try {
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            $roles = $this->roleRepo->getAllActiveRoles();

            $userSelect = ['*'];
            $userWith = ['accountDetail'];
            $userDetail = $this->userRepo->findUserDetailById($id, $userSelect, $userWith);
            $leaveTypes = $this->leaveTypeRepository->getGenderSpecificPaidLeaveTypes($userDetail->branch_id,$userDetail->gender);
            $employeeLeaveTypes = $this->employeeLeaveTypeRepository->getAll(['id', 'leave_type_id', 'days', 'is_active'], $id);
            $bsEnabled = AppHelper::ifDateInBsEnabled();

            $filteredPosts = isset($userDetail->department_id)
                ? $this->postRepository->getAllActivePostsByDepartmentId($userDetail->department_id, [], ['id', 'post_name'])
                : [];

            $filteredSupervisor = isset($userDetail->department_id)
                ? $this->userRepo->getAllActiveEmployeeByDepartment($userDetail->department_id, ['id','name'])
                : [];

            return view($this->view . 'edit', compact('companyDetail', 'roles', 'userDetail', 'leaveTypes', 'employeeLeaveTypes', 'bsEnabled','filteredSupervisor','filteredPosts'));
        } catch (Exception $exception) {

            return redirect()->back()->with('danger', $exception->getFile());
        }
    }

    public function update(UserUpdateRequest $request, UserAccountRequest $accountRequest, UserLeaveTypeRequest $leaveRequest, $id)
    {
        $this->authorize('edit_employee');
        try {
            $validatedData = $request->validated();

            if (env('DEMO_MODE', false) && (in_array($id, [1, 2]))) {
                throw new Exception(__('message.add_company_warning'), 400);
            }

            $accountValidatedData = $accountRequest->validated();

            $leaveTypeData = $leaveRequest->validated();


            $userDetail = $this->userRepo->findUserDetailById($id);
            if (in_array($userDetail->username, User::DEMO_USERS_USERNAME)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            if (!$userDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }
            // Create full name from surname, first_name, middle_name
            $nameParts = array_filter([
                $validatedData['surname'] ?? '',
                $validatedData['first_name'] ?? '',
                $validatedData['middle_name'] ?? ''
            ]);
            $validatedData['name'] = implode(' ', $nameParts);
            $validatedData['allow_holiday_check_in'] = isset($validatedData['allow_holiday_check_in']) ? 1 : 0;
            DB::beginTransaction();
            $this->userRepo->update($userDetail, $validatedData);
            $this->accountRepo->createOrUpdate($userDetail, $accountValidatedData);

            if (!is_null($validatedData['leave_allocated']) && isset($leaveTypeData['leave_type_id'])) {
                foreach ($leaveTypeData['leave_type_id'] as $key => $value) {
                    $input['days'] = $leaveTypeData['days'][$key];
                    $input['is_active'] = $leaveTypeData['is_active'][$key] ?? 0;

                    $employeeLeaveTypeData = $this->employeeLeaveTypeRepository->findByLeaveType($id, $value);
                    if ($employeeLeaveTypeData) {

                        $this->employeeLeaveTypeRepository->update($employeeLeaveTypeData, $input);
                    } else {
                        $input['employee_id'] = $id;
                        $input['leave_type_id'] = $value;


                        $this->employeeLeaveTypeRepository->store($input);
                    }
                }
            } else {
                $this->employeeLeaveTypeRepository->deleteByEmployee($id);
            }


            DB::commit();
            return redirect()
                ->route('admin.employees.index')
                ->with('success', __('message.update_user'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit_employee');
        try {
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            DB::beginTransaction();
            $this->userRepo->toggleIsActiveStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.user_is_active_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }



    public function delete($id)
    {
        $this->authorize('delete_employee');
        try {

            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            $usersDetail = $this->userRepo->findUserDetailById($id);

            if (!$usersDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }

            if ($usersDetail->id == auth()->user()->id) {
                throw new Exception(__('message._delete_own'), 402);
            }

            DB::beginTransaction();
            $this->userRepo->delete($usersDetail);
            DB::commit();
            return redirect()->back()->with('success', __('message.user_remove'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function changeWorkSpace($id)
    {
        $this->authorize('edit_employee');
        try {
            $select = ['id', 'workspace_type'];
            $userDetail = $this->userRepo->findUserDetailById($id, $select);
            if (!$userDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }
            DB::beginTransaction();
            $this->userRepo->changeWorkSpace($userDetail);
            DB::commit();
            return redirect()->back()->with('success', __('message.workspace_change'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function getAllCompanyEmployeeDetail($branchId)
    {
        try {

            $branch = $this->branchRepository->findBranchDetailById($branchId);

            $selectEmployee = ['id', 'name'];
            $selectOfficeTime = ['id', 'opening_time', 'closing_time'];
            $employees = $this->userRepo->getAllVerifiedEmployeeOfCompany($selectEmployee);
            $officeTime = $this->officeTimeRepo->getALlActiveOfficeTimeByCompanyId($branch->company_id, $selectOfficeTime);

            return response()->json([
                'employee' => $employees,
                'officeTime' => $officeTime
            ]);
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
    public function getAllBranchEmployees($branchId)
    {
        try {

            $selectEmployee = ['id', 'name'];
            $employees = $this->userRepo->getActiveEmployeeOfBranch($branchId, $selectEmployee);


            return response()->json([
                'employee' => $employees,
            ]);
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function changePassword(ChangePasswordRequest $request, $userId)
    {
        $this->authorize('change_password');
        try {
            $validatedData = $request->validated();
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }

            $userDetail = $this->userRepo->findUserDetailById($userId);

            if (!$userDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }
            DB::beginTransaction();
            $this->userRepo->changePassword($userDetail, $validatedData['new_password']);
            DB::commit();
            return redirect()->back()->with('success', __('message.user_password_change'));

        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function forceLogOutEmployee($employeeId)
    {
        $this->authorize('force_logout');
        try {
            $tokenRepository = app(TokenRepository::class);
            $refreshTokenRepository = app(RefreshTokenRepository::class);

            $userDetail = $this->userRepo->findUserDetailById($employeeId);
            if (!$userDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }
            $accessToken = $userDetail->tokens;
            DB::beginTransaction();
            foreach ($accessToken as $token) {
                $tokenRepository->revokeAccessToken($token->id);
                $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
            }
            $validatedData['uuid'] = null;
            $validatedData['logout_status'] = 0;
            $validatedData['remember_token'] = null;
            $validatedData['fcm_token'] = null;
            $this->userRepo->update($userDetail, $validatedData);
            DB::commit();
            return redirect()->back()->with('success', __('message.force_logout'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function deleteEmployeeLeaveType($id)
    {
        $this->authorize('delete_employee');
        try {
            $employeeLeaveType = $this->employeeLeaveTypeRepository->find($id);

            if (!$employeeLeaveType) {
                throw new Exception(__('message.employee_leave_not_found'), 404);
            }

            DB::beginTransaction();
            $this->employeeLeaveTypeRepository->delete($employeeLeaveType);
            DB::commit();
            return redirect()->back()->with('success', __('message.employee_leave_removed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function getAllEmployeeByDepartmentId($departmentId): JsonResponse|RedirectResponse
    {
        try {

            $select = ['name', 'id'];
            $users = $this->userRepo->getAllActiveEmployeeOfDepartment($departmentId, $select);
            return response()->json([
                'data' => $users
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function fetchEmployeesByDepartment(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $departmentIds = $request->input('department_ids');
            $select = ['name', 'id'];

            $employees = $this->userRepo->getActiveEmployeesByDepartment($departmentIds, $select);

            return response()->json($employees);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }
    public function fetchDepartmentEmployees(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $departmentIds = $request->input('department_ids');
            $select = ['name', 'id'];

            $employees = $this->userRepo->getActiveEmployeesFromDepartments($departmentIds, $select);

            return response()->json($employees);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

//    public function export()
//    {
//        $fileName = 'users.csv';
//        return \Maatwebsite\Excel\Facades\Excel::download(new UserExport, $fileName);
//    }

    /**
     * @param $branchId
     * @return JsonResponse
     */
    public function getBranchEmployeeData($branchId)
    {
        try {

            $users = $this->userRepo->getAllBranchUsers($branchId, ['id','name']);

            return response()->json([
                'users' => $users,
            ]);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }

    }

    public function toggleHolidayCheckIn($id)
    {
        $this->authorize('edit_employee');
        try {
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            DB::beginTransaction();
            $this->userRepo->toggleHolidayCheckIn($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.user_allow_holiday_check_in_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function logs(Request $request)
    {
        $this->authorize('list_employee');
        try {
            $bsEnabled = AppHelper::ifDateInBsEnabled();
            $filterData = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'date' =>  $request->date ?? ( $bsEnabled ? AppHelper::getCurrentDateInBS()  : date('Y-m-d')),
            ];

            if (!auth('admin')->check() && auth()->check()) {
                $filterData['branch_id'] = auth()->user()->branch_id;
            }

            $logData = $this->userRepo->getLocationLogs($filterData);


            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);

            return view($this->view . 'log', compact('logData', 'companyDetail', 'filterData','bsEnabled'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
}
