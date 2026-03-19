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


    public function __construct(
        protected UserRepository              $userRepo,
        protected CompanyRepository           $companyRepo,
        protected RoleRepository              $roleRepo,
        protected OfficeTimeRepository        $officeTimeRepo,
        protected UserAccountRepository       $accountRepo,
        protected BranchRepository            $branchRepository,
        protected LeaveTypeRepository         $leaveTypeRepository,
        protected EmployeeLeaveTypeRepository $employeeLeaveTypeRepository,
        protected PostRepository $postRepository,
        protected EmployeeCardSettingService $cardTemplateService

    ) {}

    /**
     * @throws AuthorizationException
     */

    public function index(Request $request)
    {
        $this->authorize('list_employee');
        try {
            $filterParameters = [
                'employee_code'   => $request->employee_code ?? null,
                'branch_id'       => $request->branch_id ?? null,
                'department_id'   => $request->department_id ?? null,
                'office_time_id'  => $request->office_time_id ?? null,
                'employment_type' => $request->employment_type ?? null,
                'post_id'         => $request->post_id ?? null,
                'supervisor_id'   => $request->supervisor_id ?? null,
            ];

            if (!auth('admin')->check() && auth()->check()) {
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $with = ['branch:id,name', 'company:id,name', 'post:id,post_name', 'department:id,dept_name', 'role:id,name', 'officeTime:id,shift,opening_time,closing_time', 'supervisor:id,name', 'accountDetail'];

            $select = ['users.*', 'branch_id', 'company_id', 'department_id', 'post_id', 'role_id'];
            $users = $this->userRepo->getAllUsers($filterParameters, $select, $with);

            $company = $this->companyRepo->getCompanyDetail(['id']);
            $branches = $this->branchRepository->getLoggedInUserCompanyBranches($company->id, ['id', 'name']);

            if ($request->input('action') == 'export') {
                $fileName = 'employees_' . date('Y-m-d_His') . '.csv';
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
            $leaveTypes = $this->leaveTypeRepository->getGenderSpecificPaidLeaveTypes($userDetail->branch_id, $userDetail->gender);
            $employeeLeaveTypes = $this->employeeLeaveTypeRepository->getAll(['id', 'leave_type_id', 'days', 'is_active'], $id);
            $bsEnabled = AppHelper::ifDateInBsEnabled();

            $filteredPosts = isset($userDetail->department_id)
                ? $this->postRepository->getAllActivePostsByDepartmentId($userDetail->department_id, [], ['id', 'post_name'])
                : [];

            $filteredSupervisor = isset($userDetail->department_id)
                ? $this->userRepo->getAllActiveEmployeeByDepartment($userDetail->department_id, ['id', 'name'])
                : [];

            return view($this->view . 'edit', compact('companyDetail', 'roles', 'userDetail', 'leaveTypes', 'employeeLeaveTypes', 'bsEnabled', 'filteredSupervisor', 'filteredPosts'));
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

    //old
    // public function delete($id)
    // {
    //     $this->authorize('delete_employee');
    //     try {

    //         if (env('DEMO_MODE', false)) {
    //             throw new Exception(__('message.add_company_warning'), 400);
    //         }
    //         $usersDetail = $this->userRepo->findUserDetailById($id);

    //         if (!$usersDetail) {
    //             throw new Exception(__('message.user_not_found'), 404);
    //         }

    //         $authUser = auth('admin')->user() ?? auth()->user();
    //         if ($usersDetail->id == optional($authUser)->id) {
    //             throw new Exception(__('message._delete_own'), 402);
    //         }

    //         DB::beginTransaction();
    //         $this->userRepo->delete($usersDetail);
    //         DB::commit();
    //         return redirect()->back()->with('success', __('message.user_remove'));
    //     } catch (Exception $exception) {
    //         DB::rollBack();
    //         return redirect()->back()->with('danger', $exception->getMessage());
    //     }
    // }

    //new
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

            $authUser = auth('admin')->user() ?? auth()->user();
            if ($usersDetail->id == optional($authUser)->id) {
                throw new Exception(__('message._delete_own'), 402);
            }

            DB::beginTransaction();

            // DELETE EMPLOYEE ACCOUNT (BVN, bank details)
            DB::table('employee_accounts')->where('user_id', $id)->delete();

            // DELETE EMPLOYEE LEAVE TYPES (allocated leaves)
            \App\Models\EmployeeLeaveType::where('employee_id', $id)->delete();

            // DELETE EMPLOYEE SALARY (salary records)
            DB::table('employee_salaries')->where('employee_id', $id)->delete();

            // SOFT DELETE USER (with email/username obfuscation)
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

            $users = $this->userRepo->getAllBranchUsers($branchId, ['id', 'name']);

            return response()->json([
                'users' => $users,
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
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
                'date' =>  $request->date ?? ($bsEnabled ? AppHelper::getCurrentDateInBS()  : date('Y-m-d')),
            ];

            if (!auth('admin')->check() && auth()->check()) {
                $filterData['branch_id'] = auth()->user()->branch_id;
            }

            $logData = $this->userRepo->getLocationLogs($filterData);


            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);

            return view($this->view . 'log', compact('logData', 'companyDetail', 'filterData', 'bsEnabled'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }



    /**
    * Download CSV template for bulk employee upload ✅
    */
    public function downloadTemplate()
    {
        $this->authorize('create_employee');

        try {
            // CSV Headers matching "Employee Data Filter" table
            $headers = [
                'employee_id',
                'surname',
                'first_name',
                'middle_name',
                'nin',
                'bvn',
                'date_of_birth',
                'phone_no',
                'email',
                'employment_date',
                'employment_type',
                'supervisor',
                'branch_company',
                'department',
                'designation',
                'grade_level',
                'tax_id',
                'sbu_code',
                'rsa_no',
                'hmo_id',
                'shift',
                'bank_name',
                'account_no',
                'account_type',
                'account_holder',
                'workplace',
            ];

            // Create CSV content
            $csv = fopen('php://temp', 'r+');

            // Add headers
            fputcsv($csv, $headers);

            // Add example row
            fputcsv($csv, [
                'PJD-00099',                    // employee_id
                'Doe',                          // surname
                'John',                         // first_name
                'Michael',                      // middle_name
                '12345678901',                  // nin
                'BVN123456789',                 // bvn
                '1990-05-20',                   // date_of_birth (YYYY-MM-DD)
                '08012345678',                  // phone_no
                'john.doe@example.com',         // email
                '2024-01-15',                   // employment_date (YYYY-MM-DD)
                'Permanent',                    // employment_type
                'MR TEST BUD',                  // supervisor (must match existing employee name)
                'Patjeda Group (PG)',           // branch_company (must match existing branch)
                'Operations',                   // department (must match existing department)
                'Fleet Supervisor',             // designation
                'Senior Staff',                 // grade_level
                'TAX123',                       // tax_id
                'SBU123',                       // sbu_code
                'RSA123',                       // rsa_no
                'HMO123',                       // hmo_id
                '10:17 AM - 8:00 PM',          // shift
                'UBA',                          // bank_name
                '1234567890',                   // account_no
                'current',                      // account_type
                'John Doe',                     // account_holder
                'Office',                       // workplace
            ]);



            rewind($csv);
            $csvContent = stream_get_contents($csv);
            fclose($csv);

            $filename = 'employee_upload_template_' . date('Y-m-d') . '.csv';

            return response($csvContent, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);

        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
    * bulk employee upload ✅
    */
    public function bulkUpload(Request $request)
    {
        $this->authorize('create_employee');

        try {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:5120',
            ]);

            $file = $request->file('csv_file');
            $handle = fopen($file->getRealPath(), 'r');
            $headers = fgetcsv($handle);

            if (!$headers) {
                return redirect()->back()->with('danger', 'Invalid CSV file format.');
            }

            $successCount = 0;
            $errors = [];
            $rowNumber = 1;
            $companyId = AppHelper::getAuthUserCompanyId();

            DB::beginTransaction();

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if (empty(array_filter($row))) {
                    continue;
                }

                $data = array_combine($headers, $row);

                // Validate (ignore soft-deleted records)
                // $validator = \Illuminate\Support\Facades\Validator::make($data, [
                //     'email' => [
                //         'required',
                //         'email',
                //         \Illuminate\Validation\Rule::unique('users', 'email')->whereNull('deleted_at')
                //     ],
                //     'phone_no' => [
                //         'required',
                //         \Illuminate\Validation\Rule::unique('users', 'phone')->whereNull('deleted_at')
                //     ],
                //     'employee_id' => [
                //         'nullable',
                //         'regex:/^' . AppHelper::getEmployeeCodePrefix() . '-\d{5}$/',
                //         \Illuminate\Validation\Rule::unique('users', 'employee_code')->whereNull('deleted_at')
                //     ],
                //     'surname' => 'required|string',
                //     'first_name' => 'required|string',
                //     'employment_date' => 'required|date_format:Y-m-d',
                //     'date_of_birth' => 'nullable|date_format:Y-m-d',
                // ], [
                //     'employee_id.regex' => 'Employee ID must be in format ' . AppHelper::getEmployeeCodePrefix() . '-XXXXX (e.g., ' . AppHelper::getEmployeeCodePrefix() . '-00001)',
                //     'employee_id.unique' => 'The employee id has already been taken.',
                // ]);

                // Validate required fields + unique personal/financial IDs
                $validator = \Illuminate\Support\Facades\Validator::make($data, [
                    'email' => [
                        'required',
                        'email',
                        \Illuminate\Validation\Rule::unique('users', 'email')->whereNull('deleted_at')
                    ],
                    'phone_no' => [
                        'nullable',
                        \Illuminate\Validation\Rule::unique('users', 'phone')->whereNull('deleted_at')
                    ],
                    'employee_id' => [
                        'nullable',
                        'regex:/^' . AppHelper::getEmployeeCodePrefix() . '-\d{5}$/',
                        \Illuminate\Validation\Rule::unique('users', 'employee_code')->whereNull('deleted_at')
                    ],
                    'nin' => [
                        'required',
                        \Illuminate\Validation\Rule::unique('users', 'nin')->whereNull('deleted_at')
                    ],
                    'bvn' => [
                        'required',
                        \Illuminate\Validation\Rule::unique('employee_accounts', 'bvn')->whereNotNull('bvn')
                    ],
                    'tax_id' => [
                        'nullable',
                        \Illuminate\Validation\Rule::unique('users', 'tax_id')->whereNull('deleted_at')->whereNotNull('tax_id')
                    ],
                    'rsa_no' => [
                        'nullable',
                        \Illuminate\Validation\Rule::unique('users', 'rsa_no')->whereNull('deleted_at')->whereNotNull('rsa_no')
                    ],
                    'hmo_id' => [
                        'nullable',
                        \Illuminate\Validation\Rule::unique('users', 'hmo_id')->whereNull('deleted_at')->whereNotNull('hmo_id')
                    ],
                    'account_no' => [
                        'nullable',
                        \Illuminate\Validation\Rule::unique('employee_accounts', 'bank_account_no')->whereNotNull('bank_account_no')
                    ],
                    'surname' => 'required|string',
                    'first_name' => 'required|string',
                    'employment_date' => 'nullable|date_format:Y-m-d',
                    'date_of_birth' => 'nullable|date_format:Y-m-d',
                ], [
                    'employee_id.regex' => 'Employee ID must be in format ' . AppHelper::getEmployeeCodePrefix() . '-XXXXX (5 digits)',
                    'employee_id.unique' => 'The employee id has already been taken.',
                    'email.unique' => 'The email has already been taken.',
                    'phone_no.unique' => 'The phone number has already been taken.',
                    'nin.required' => 'NIN is required.',
                    'nin.unique' => 'The NIN has already been taken.',
                    'bvn.required' => 'BVN is required.',
                    'bvn.unique' => 'The BVN has already been taken.',
                    'tax_id.unique' => 'The Tax ID has already been taken.',
                    'rsa_no.unique' => 'The RSA No has already been taken.',
                    'hmo_id.unique' => 'The HMO ID has already been taken.',
                    'account_no.unique' => 'The bank account number has already been taken.',
                    'employment_date.date_format' => 'The employment date does not match the format Y-m-d.',
                    'date_of_birth.date_format' => 'The date of birth does not match the format Y-m-d.',
                ]);


                if ($validator->fails()) {
                    $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // ✅ ADDITIONAL VALIDATION: Check BVN uniqueness within CSV file
                if (!empty($data['bvn'])) {
                    // Initialize tracking array on first iteration
                    if (!isset($uploadedBvns)) {
                        $uploadedBvns = [];
                    }

                    // Check if BVN is duplicated within the CSV file
                    if (in_array($data['bvn'], $uploadedBvns)) {
                        $errors[] = "Row {$rowNumber}: The BVN is duplicated within the CSV file.";
                        continue;
                    }

                    // Add to tracking array
                    $uploadedBvns[] = $data['bvn'];
                }

                // Find Branch
                $branch = \App\Models\Branch::where('name', $data['branch_company'])
                    ->where('company_id', $companyId)
                    ->first();

                if (!$branch) {
                    $errors[] = "Row {$rowNumber}: Branch '{$data['branch_company']}' not found";
                    continue;
                }

                // Find Department
                $department = \App\Models\Department::where('dept_name', $data['department'])
                    ->where('branch_id', $branch->id)
                    ->first();

                if (!$department) {
                    $errors[] = "Row {$rowNumber}: Department '{$data['department']}' not found";
                    continue;
                }

                // Find Supervisor
                $supervisorId = null;
                if (!empty($data['supervisor']) && $data['supervisor'] != 'N/A') {
                    $supervisor = \App\Models\User::where('name', $data['supervisor'])
                        ->where('company_id', $companyId)
                        ->whereNull('deleted_at')
                        ->first();
                    if ($supervisor) {
                        $supervisorId = $supervisor->id;
                    }
                }

                // Get employee role
                $employeeRole = \App\Models\Role::where('name', 'employee')->first();

                if (!$employeeRole) {
                    $errors[] = "Row {$rowNumber}: Default 'employee' role not found";
                    continue;
                }

                // Find Post
                $postId = null;
                if (!empty($data['designation']) && $data['designation'] != 'N/A') {
                    $post = \App\Models\Post::where('post_name', $data['designation'])->first();
                    if ($post) {
                        $postId = $post->id;
                    }
                }

                // Find Shift
                $officeTimeId = null;
                if (!empty($data['shift']) && $data['shift'] != 'N/A') {
                    $shiftParts = explode(' - ', $data['shift']);

                    if (count($shiftParts) == 2) {
                        $openingTime = trim($shiftParts[0]);
                        $closingTime = trim($shiftParts[1]);

                        // Get all office times and manually match
                        $allOfficeTimes = \App\Models\OfficeTime::where('company_id', $companyId)
                            ->where('is_active', 1)
                            ->get();

                        // Find the matching one
                        foreach ($allOfficeTimes as $ot) {
                            if ($ot->opening_time === $openingTime && $ot->closing_time === $closingTime) {
                                $officeTimeId = $ot->id;
                                break;
                            }
                        }
                    }
                }


                // Prepare data
                $fullName = trim($data['first_name'] . ' ' . ($data['middle_name'] ?? '') . ' ' . $data['surname']);
                $email = strtolower(trim($data['email']));
                $username = strtolower(trim(explode('@', $email)[0]));

                // Get employee code - auto generate only if empty or invalid format
                $employeeCode = null;
                if (!empty($data['employee_id'])) {
                    // Validate format
                    $prefix = AppHelper::getEmployeeCodePrefix();
                    if (preg_match('/^' . $prefix . '-\d{5}$/', $data['employee_id'])) {
                        $employeeCode = $data['employee_id'];
                    }
                }
                // If still null, auto-generate
                if (!$employeeCode) {
                    $employeeCode = AppHelper::getEmployeeCode();
                }

                // Create User
                $user = \App\Models\User::create([
                    'name' => $fullName,
                    'surname' => trim($data['surname']),
                    'first_name' => trim($data['first_name']),
                    'middle_name' => !empty($data['middle_name']) ? trim($data['middle_name']) : null,
                    'email' => $email,
                    'phone' => trim($data['phone_no']),
                    'username' => $username,
                    'password' => bcrypt('password123'),
                    'employee_code' => $employeeCode, // USE THE VALIDATED/GENERATED CODE
                    'company_id' => $companyId,
                    'branch_id' => $branch->id,
                    'department_id' => $department->id,
                    'post_id' => $postId,
                    'role_id' => $employeeRole->id,
                    'supervisor_id' => $supervisorId,
                    'designation' => !empty($data['designation']) ? $data['designation'] : null,
                    'employment_type' => !empty($data['employment_type']) ? ucfirst(strtolower(trim($data['employment_type']))) : 'Permanent',
                    // 'joining_date' => $data['employment_date'],
                    'joining_date' => !empty($data['employment_date']) ? $data['employment_date'] : null,
                    'dob' => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
                    'nin' => $data['nin'] ?? null,
                    'gender' => 'male',
                    'marital_status' => 'unmarried',
                    'address' => null,
                    'avatar' => null,
                    'remarks' => 'Imported via CSV on ' . date('Y-m-d H:i:s'),
                    'is_active' => 1,
                    'status' => 'verified',
                    'office_time_id' => $officeTimeId,
                    'workspace_type' => !empty($data['workplace']) && strtolower(trim($data['workplace'])) == 'field' ? 0 : 1, // Field=0, Office=1
                    'grade_level' => !empty($data['grade_level']) ? trim($data['grade_level']) : null,
                    'tax_id' => !empty($data['tax_id']) ? trim($data['tax_id']) : null,
                    'sbu_code' => !empty($data['sbu_code']) ? trim($data['sbu_code']) : null,
                    'rsa_no' => !empty($data['rsa_no']) ? trim($data['rsa_no']) : null,
                    'hmo_id' => !empty($data['hmo_id']) ? trim($data['hmo_id']) : null,
                ]);

                // Create Account
                $this->accountRepo->store([
                    'user_id' => $user->id,
                    'salary_cycle' => 'monthly',
                    'bank_name' => $data['bank_name'] ?? null,
                    'bank_account_no' => $data['account_no'] ?? null,
                    'bank_account_type' => !empty($data['account_type']) ? strtolower(trim($data['account_type'])) : null, // LOWERCASE
                    'account_holder' => $data['account_holder'] ?? null,
                    'bvn' => $data['bvn'] ?? null,
                ]);

                $successCount++;
            }

            fclose($handle);
            DB::commit();

            $message = "{$successCount} employee(s) uploaded successfully.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " row(s) had errors.";
            }

            return redirect()->back()
                ->with('success', $message)
                ->with('upload_errors', $errors);

        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
}
