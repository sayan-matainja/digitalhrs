<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use App\Helpers\PMHelper;
use App\Models\Award;
use App\Models\EmployeeLocation;
use App\Models\Event;
use App\Models\Training;
use App\Models\User;
use App\Traits\ImageService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    const IS_ACTIVE = 1;

    const STATUS_VERIFIED = 'verified';

    const ADMIN = 'admin';

    const ONLINE = 1;


    use ImageService;

    public function getAllUsers($filterParameters, $select = ['*'], $with = [])
    {
    $userList = User::with($with)
        ->select($select)
        ->when(!empty($filterParameters['employee_code']), function ($query) use ($filterParameters) {
            $query->where('employee_code', 'like', '%' . $filterParameters['employee_code'] . '%');
        })
        ->when(!empty($filterParameters['employee_name']), function ($query) use ($filterParameters) {
            $query->where('name', 'like', '%' . $filterParameters['employee_name'] . '%');
        })
        ->when(!empty($filterParameters['surname']), function ($query) use ($filterParameters) {
            $query->where('surname', 'like', '%' . $filterParameters['surname'] . '%');
        })
        ->when(!empty($filterParameters['first_name']), function ($query) use ($filterParameters) {
            $query->where('first_name', 'like', '%' . $filterParameters['first_name'] . '%');
        })
        ->when(!empty($filterParameters['middle_name']), function ($query) use ($filterParameters) {
            $query->where('middle_name', 'like', '%' . $filterParameters['middle_name'] . '%');
        })
        ->when(!empty($filterParameters['email']), function ($query) use ($filterParameters) {
            $query->where('email', 'like', '%' . $filterParameters['email'] . '%');
        })
        ->when(!empty($filterParameters['phone']), function ($query) use ($filterParameters) {
            $query->where('phone', $filterParameters['phone']);
        })
        ->when(!empty($filterParameters['nin']), function ($query) use ($filterParameters) {
            $query->where('nin', 'like', '%' . $filterParameters['nin'] . '%');
        })
        ->when(!empty($filterParameters['dob']), function ($query) use ($filterParameters) {
            $query->whereDate('dob', $filterParameters['dob']);
        })
        ->when(!empty($filterParameters['employment_type']), function ($query) use ($filterParameters) {
            $query->where('employment_type', $filterParameters['employment_type']);
        })
        ->when(!empty($filterParameters['joining_date']), function ($query) use ($filterParameters) {
            $query->whereDate('joining_date', $filterParameters['joining_date']);
        })
        ->when(!empty($filterParameters['grade_level']), function ($query) use ($filterParameters) {
            $query->where('grade_level', 'like', '%' . $filterParameters['grade_level'] . '%');
        })
        ->when(!empty($filterParameters['tax_id']), function ($query) use ($filterParameters) {
            $query->where('tax_id', 'like', '%' . $filterParameters['tax_id'] . '%');
        })
        ->when(!empty($filterParameters['sbu_code']), function ($query) use ($filterParameters) {
            $query->where('sbu_code', 'like', '%' . $filterParameters['sbu_code'] . '%');
        })
        ->when(!empty($filterParameters['rsa_no']), function ($query) use ($filterParameters) {
            $query->where('rsa_no', 'like', '%' . $filterParameters['rsa_no'] . '%');
        })
        ->when(!empty($filterParameters['hmo_id']), function ($query) use ($filterParameters) {
            $query->where('hmo_id', 'like', '%' . $filterParameters['hmo_id'] . '%');
        })
        ->when(!empty($filterParameters['supervisor_id']), function ($query) use ($filterParameters) {
            $query->where('supervisor_id', $filterParameters['supervisor_id']);
        })
        ->when(!empty($filterParameters['office_time_id']), function ($query) use ($filterParameters) {
            $query->where('office_time_id', $filterParameters['office_time_id']);
        })
        ->when(!empty($filterParameters['branch_id']), function ($query) use ($filterParameters) {
            $query->where('branch_id', $filterParameters['branch_id']);
        })
        ->when(!empty($filterParameters['department_id']), function ($query) use ($filterParameters) {
            $query->where('department_id', $filterParameters['department_id']);
        })
        ->when(!empty($filterParameters['post_id']), function ($query) use ($filterParameters) {
            $query->where('post_id', $filterParameters['post_id']);
        })
        ->when(!empty($filterParameters['bvn']), function ($query) use ($filterParameters) {
            $query->whereHas('accountDetail', function ($q) use ($filterParameters) {
                $q->where('bvn', 'like', '%' . $filterParameters['bvn'] . '%');
            });
        });

    return $userList->orderBy('users.name')
        ->latest()
        ->paginate(getRecordPerPage());
    }

    public function getAllCompanyUsers($select = ['*'],$with=[])
    {
        return User::select($select)
            ->with($with)
            ->where('status', 'verified')
            ->where('is_active', self::IS_ACTIVE)
            ->get();
    }

    public function getAllBranchUsers($branchId, $select = ['*'])
    {
        return User::select($select)
            ->where('branch_id', $branchId)
            ->where('status', 'verified')
            ->where('is_active', self::IS_ACTIVE)
            ->get();
    }

    public function getAllVerifiedEmployeeOfCompany($select = ['*'], $with = [])
    {

       return User::select($select)->with($with)
            ->where('status', 'verified')
            ->where('is_active', self::IS_ACTIVE)
           ->get();
    }

    public function getAllVerifiedEmployeesExceptAdminOfCompany($select = ['*'], $with = [])
    {

        return User::select($select)->with($with)
            ->where('status', 'verified')
            ->where('is_active', self::IS_ACTIVE)
            ->get();
    }

    public function getAllEmployeesForPayroll()
    {

        return User::select('users.id', 'users.name')
            ->leftJoin('employee_salaries', 'users.id', 'employee_salaries.employee_id')
            ->notAdmin()
            ->where('users.status', 'verified')
            ->where('users.is_active', self::IS_ACTIVE)
            ->whereNotNull('employee_salaries.employee_id')
            ->get();
    }

    public function store($validatedData)
    {
        $validatedData['created_by'] = getAuthUserCode() ?? null;
        $validatedData['avatar'] = $this->storeImage($validatedData['avatar'], User::AVATAR_UPLOAD_PATH, 500, 500);
        return User::create($validatedData)->fresh();
    }

    public function changePassword($userDetail, $newPassword)
    {
        return $userDetail->update([
            'password' => bcrypt($newPassword)
        ]);
    }

    public function delete($userDetail)
    {
        if ($userDetail['avatar']) {
            $this->removeImage(User::AVATAR_UPLOAD_PATH, $userDetail['avatar']);
        }

        $updateData = [
            'email'=>uniqid().$userDetail->email,
            'username'=>uniqid().$userDetail->username,
        ];
        $this->update($userDetail, $updateData);

        return $userDetail->delete();
    }

    public function update($userDetail, $validatedData)
    {
        if (isset($validatedData['avatar'])) {
            if ($userDetail['avatar']) {
                $this->removeImage(User::AVATAR_UPLOAD_PATH, $userDetail['avatar']);
            }
            $validatedData['avatar'] = $this->storeImage($validatedData['avatar'], User::AVATAR_UPLOAD_PATH, 500, 500);
        }
        return $userDetail->update($validatedData);
    }

    public function updateProfileForApi($userDetail, $validatedData)
    {
        if (isset($validatedData['avatar'])) {
            $this->removeImage(User::AVATAR_UPLOAD_PATH, $userDetail['avatar']);
        }
        $userDetail->update($validatedData);
        return $userDetail;
    }

    public function toggleIsActiveStatus($id)
    {
        $userDetail = $this->findUserDetailById($id);
        return $userDetail->update([
            'is_active' => !$userDetail->is_active,
        ]);
    }
    public function toggleHolidayCheckIn($id)
    {
        $userDetail = $this->findUserDetailById($id);
        return $userDetail->update([
            'allow_holiday_check_in' => !$userDetail->allow_holiday_check_in,
        ]);
    }

    public function findUserDetailById($id, $select = ['*'], $with = [])
    {
        return User::with($with)->select($select)->where('id', $id)->first();
    }
    public function findByEmployeeCode($employeeCode,$select = ['*'], $with = [])
    {
        return User::with($with)->select($select)->where('employee_code','like', '%'.$employeeCode)->first();
    }

    public function changeWorkSpace($userDetail)
    {
        return $userDetail->update([
            'workspace_type' => !$userDetail->workspace_type,
        ]);
    }

    public function updateUserOnlineStatus($userDetail, $loginStatus)
    {
        return $userDetail->update([
            'online_status' => $loginStatus,
        ]);
    }

    public function getUserByUserName($userName, $select = ['*'])
    {
        return User::select($select)
            ->where('username', $userName)
            ->where('is_active', self::IS_ACTIVE)
            ->where('status', 'verified')

            ->first();
    }

    public function getUserByUserEmail($userEmail, $select = ['*'])
    {
        return User::select($select)
            ->where('email', $userEmail)
            ->where('is_active', self::IS_ACTIVE)
            ->where('status', 'verified')
            ->first();
    }

    public function getEmployeeAttendanceDetailOfTheMonth($filterParameter, $select, $with)
    {
        return User::with($with)
            ->select($select)
            ->where('id', $filterParameter['user_id'])
            ->with('employeeAttendance', function ($query) use ($filterParameter) {
                $query->with('officeTime');
                if(isset($filterParameter['start_date'])){
                    $query->whereBetween('attendance_date', [$filterParameter['start_date'],$filterParameter['end_date']]);
                }else{
                    $query->whereMonth('attendance_date', $filterParameter['month'])
                        ->whereYear('attendance_date', $filterParameter['year']);
                }
                $query->orderBy('attendance_date', 'asc');
            })
            ->first();

    }
    public function getApiEmployeeAttendanceDetailOfTheMonth($filterParameter, $select, $with)
    {
        return User::with($with)
            ->select($select)
            ->where('id', $filterParameter['user_id'])
            ->with('employeeAttendance', function ($query) use ($filterParameter) {
                $query->with('officeTime');
                if(isset($filterParameter['start_date'])){
                    $query->whereBetween('attendance_date', [$filterParameter['start_date'],$filterParameter['end_date']]);
                }
                $query->orderBy('attendance_date', 'asc');
            })
            ->first();

    }

    public function getEmployeeOverviewDetail($employeeId, $date)
    {
        $leaveDates = AppHelper::leaveYearDetailToFilterData();
        $totalLeaveAllocated = DB::table('leave_types')
            ->select('company_id', DB::raw('sum(leave_allocated) as total_paid_leaves'))
            ->whereNotNull('leave_allocated')
            ->where('is_active', 'self::IS_ACTIVE')
            ->groupBy('company_id');

        $totalAssignedProjectCountQuery = DB::table('projects')
            ->select(DB::raw('COUNT(DISTINCT projects.id) as total_projects'))
            ->leftJoin('assigned_members', 'projects.id', '=', 'assigned_members.assignable_id')
            ->leftJoin('project_team_leaders', 'projects.id', '=', 'project_team_leaders.project_id')
            ->where('projects.is_active', self::IS_ACTIVE)
            ->where(function ($query) use ($employeeId) {
                $query->where('assigned_members.member_id', $employeeId)
                    ->where('assigned_members.assignable_type','project');
            })->orWhere(function ($query) use ($employeeId){
                $query->Where('project_team_leaders.leader_id', $employeeId);
            });

        $totalPendingTaskCount = DB::table('tasks')
            ->select(DB::raw('COUNT(DISTINCT tasks.id) as total_pending_tasks'))
            ->join('projects', 'tasks.project_id', '=', 'projects.id')
            ->leftJoin('assigned_members', function($join) {
                $join->on('tasks.id', '=', 'assigned_members.assignable_id')
                    ->where('assigned_members.assignable_type', '=', 'task');
            })
            ->leftJoin('project_team_leaders', 'tasks.project_id', '=', 'project_team_leaders.project_id')
            ->whereNotIn('tasks.status', ['cancelled','completed'])
            ->where(function ($query) use ($employeeId) {
                $query->where('assigned_members.member_id', $employeeId)
                    ->orWhere('project_team_leaders.leader_id', $employeeId);
            });


        $presentDays = DB::table('attendances')
            ->select('user_id', 'company_id', DB::raw('COUNT(id) as total_present_day'))
            ->whereNotNull('check_out_at');
        if (isset($date['start_date'])) {
            $presentDays->whereBetween('attendance_date', [$date['start_date'], $date['end_date']]);
        } else {
            $presentDays->whereYear('attendance_date', $date['year']);
        }
        $presentDays->groupBy('user_id', 'company_id');


        $leaveTaken = DB::table('leave_requests_master')
            ->select('requested_by', 'company_id',   DB::raw('CAST(SUM(CAST(no_of_days AS DECIMAL(10,2))) AS DECIMAL(10,2)) AS total_leave_taken'))
            ->where('status', 'approved')
            ->where(function ($query) use ($leaveDates) {
                $query->whereBetween('leave_from', [$leaveDates['start_date'], $leaveDates['end_date']])
                    ->orWhereBetween('leave_to', [$leaveDates['start_date'], $leaveDates['end_date']])
                    ->orWhere(function ($q) use ($leaveDates) {
                        $q->whereNull('leave_to')
                            ->whereBetween('leave_from', [$leaveDates['start_date'], $leaveDates['end_date']]);
                    });
            })
        ->groupBy('requested_by', 'company_id');


        $pendingLeaves = DB::table('leave_requests_master')
            ->select('requested_by', 'company_id', DB::raw('CAST(SUM(CAST(no_of_days AS DECIMAL(10,2))) AS DECIMAL(10,2)) AS total_pending_leaves'))
            ->where('status', 'pending')
            ->where(function ($query) use ($leaveDates) {
            $query->whereBetween('leave_from', [$leaveDates['start_date'], $leaveDates['end_date']])
                ->orWhereBetween('leave_to', [$leaveDates['start_date'], $leaveDates['end_date']])
                ->orWhere(function ($q) use ($leaveDates) {
                    $q->whereNull('leave_to')
                        ->whereBetween('leave_from', [$leaveDates['start_date'], $leaveDates['end_date']]);
                });
            })
            ->groupBy('requested_by', 'company_id');


        $totalHolidays = DB::table('holidays')
            ->select('company_id', DB::raw('COUNT(id) as total_holidays'))
            ->where('is_active', self::IS_ACTIVE);
        if (isset($date['start_date'])) {
            $totalHolidays->whereBetween('event_date', [$date['start_date'], $date['end_date']]);
        } else {
            $totalHolidays->whereYear('event_date', $date['year']);
        }
        $totalHolidays->groupBy('company_id');

        $weekends = count(AppHelper::getWeekendList());

        $daysAdd = AppHelper::getAwardDisplayLimit();

        $totalAwards = Award::select('employee_id', DB::raw('COUNT(id) as total_awards'))
            ->where('employee_id', $employeeId)
            ->where(function ($query) use ($daysAdd) {
                $query->whereRaw("? BETWEEN awarded_date AND DATE_ADD(awarded_date, INTERVAL ? DAY)", [Carbon::today(), $daysAdd]);
            })
            ->groupBy('employee_id');

        $upcomingEvent = Event::select('event_users.user_id', DB::raw('COUNT(events.id) as upcoming_events'))
            ->leftJoin('event_users', 'events.id', '=', 'event_users.event_id')
            ->where('event_users.user_id', $employeeId)
            ->where(function ($query) {
                $query->where('start_date', '>=',Carbon::today())
                    ->orWhereRaw('? BETWEEN start_date AND end_date', [Carbon::today()]);
            });


        return DB::table('users')->select(
            'present_days.total_present_day',
            DB::raw('CAST(leave_taken.total_leave_taken AS DECIMAL(10,2)) as total_leave_taken'),
            DB::raw('COALESCE(holidays.total_holidays, 0) + ' . $weekends . ' as total_holidays'),
            'leave_allocated.total_paid_leaves',
            DB::raw('CAST(pending_leaves.total_pending_leaves AS DECIMAL(10,2)) as total_pending_leaves'),
            'projects.total_projects',
            'tasks.total_pending_tasks',
            'awards.total_awards',
            'events.upcoming_events'
        )
            ->leftJoinSub($presentDays, 'present_days', function ($join) {
                $join->on('users.id', '=', 'present_days.user_id');
            })
            ->leftJoinSub($leaveTaken, 'leave_taken', function ($join) {
                $join->on('users.id', '=', 'leave_taken.requested_by');
            })
            ->leftJoinSub($totalHolidays, 'holidays', function ($join) {
                $join->on('users.company_id', '=', 'holidays.company_id');
            })
            ->leftJoinSub($totalLeaveAllocated, 'leave_allocated', function ($join) {
                $join->on('users.company_id', '=', 'leave_allocated.company_id');
            })
            ->leftJoinSub($pendingLeaves, 'pending_leaves', function ($join) {
                $join->on('users.id', '=', 'pending_leaves.requested_by');
            })
            ->leftJoinSub($totalAssignedProjectCountQuery, 'projects', function ($join) {
                $join->on(DB::raw('1'), '=', DB::raw('1'));
            })
            ->leftJoinSub($totalPendingTaskCount, 'tasks', function ($join) {
                $join->on(DB::raw('1'), '=', DB::raw('1'));
            })
            ->leftJoinSub($totalAwards, 'awards', function ($join) {
                $join->on('users.id', '=', 'awards.employee_id');
            })
            ->leftJoinSub($upcomingEvent, 'events', function ($join) {
                $join->on('users.id', '=', 'events.user_id');
            })
            ->where('users.is_active', self::IS_ACTIVE)
            ->where('users.status', 'verified')
            ->whereNull('users.deleted_at')
            ->where('users.id', $employeeId)
            ->first();
    }

    public function getAllCompanyEmployeeLogOutRequest($filterData,$select = ['*'])
    {


        return User::select($select)
            ->when(isset($filterData['branch_id']), function($query) use ($filterData) {
                $query->where('branch_id', $filterData['branch_id']);
            })
            ->when(isset($filterData['department_id']), function($query) use ($filterData) {
                $query->where('department_id', $filterData['department_id']);
            })
            ->when(isset($filterData['employee_id']), function($query) use ($filterData) {
                $query->where('id', $filterData['employee_id']);
            })
            ->where('logout_status', self::IS_ACTIVE)
            ->where('status', 'verified')
            ->where('is_active', self::IS_ACTIVE)
            ->get();
    }

    public function acceptLogoutRequest($employeeId)
    {
        $userDetail = $this->findUserDetailById($employeeId);
        return $userDetail->update([
            'logout_status' => 0,
        ]);
    }

    public function findUserDetailByRole($id)
    {
        return User::where('role_id', $id)->first();
    }

    public function getUserByRole($roleId, $select=['*'])
    {
        return User::select($select)->where('role_id', $roleId)->where('status', 'verified')
            ->where('is_active', self::IS_ACTIVE)->get();
    }

    public function updateUserFcmToken($userDetail, $newFcmToken)
    {
        return $userDetail->update(['fcm_token' => $newFcmToken]);
    }

    public function deleteAccountDetail($userDetail)
    {
        return $userDetail->accountDetail->delete();
    }

    public function pluckIdAndNameOfAllVerifiedEmployee()
    {


        return User::where('status', 'verified')
            ->where('is_active', self::IS_ACTIVE)
            ->pluck('name', 'id')->toArray();
    }

    public function getAllVerifiedActiveEmployeeWithSalaryGroup($filterParameters)
    {


       return User::query()
            ->select(
                'users.id as employee_id',
                'users.name as employee_name',
                'users.email as employee_email',
                'roles.id as role_id',
                'roles.name as role_name',
                'users.marital_status as marital_status',
                'employee_accounts.id as account_id',
                'employee_accounts.salary as salary',
                'employee_accounts.salary_cycle as salary_cycle',
                'employee_accounts.allow_generate_payroll as allow_generate_payroll',
                'salary_group_employees.salary_group_id  as salary_group_id',
                'salary_groups.name  as salary_group_name',
            )
            ->join('roles','users.role_id','roles.id')
            ->leftJoin('employee_accounts','users.id','employee_accounts.user_id')
            ->leftJoin('salary_group_employees','users.id','salary_group_employees.employee_id')
            ->leftJoin('salary_groups','salary_group_employees.salary_group_id','salary_groups.id')
            ->where('users.is_active',self::IS_ACTIVE)
            ->where('users.status',self::STATUS_VERIFIED)
            ->where('roles.name','!=',self::ADMIN)
            ->when(isset($filterParameters['employee_name']), function($query) use ($filterParameters){
                $query->where('users.name', 'like', '%' . $filterParameters['employee_name'] . '%');
            })
            ->when(isset($filterParameters['department_id']), function($query) use ($filterParameters){
                $query->where('users.department_id',$filterParameters['department_id']);
            })
           ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('users.branch_id',$filterParameters['branch_id']);
            })
          ->get();
    }

    public function getEmployeeAccountDetailsToGeneratePayslip($employeeId, $filterData=[]): Collection|array
    {

        return User::query()

            ->select(
                'users.id as employee_id',
                'users.name as employee_name',
                'users.marital_status as marital_status',
                'users.joining_date',
                'employee_salaries.id as employee_salary_id',
                'employee_salaries.hour_rate',
                'employee_salaries.annual_salary as annual_salary',
                'employee_salaries.basic_salary_type',
                'employee_salaries.basic_salary_value',
                'employee_salaries.monthly_basic_salary as monthly_basic_salary',
                'employee_salaries.annual_basic_salary as annual_basic_salary',
                'employee_salaries.monthly_fixed_allowance as monthly_fixed_allowance',
                'employee_salaries.annual_fixed_allowance as annual_fixed_allowance',
                'employee_salaries.weekly_basic_salary',
                'employee_salaries.weekly_fixed_allowance',
                'employee_salaries.weekly_hours',
                'employee_salaries.monthly_hours',
                'employee_salaries.payroll_type',
                DB::raw('COALESCE(advance_salaries.is_settled, true) AS advance_settled'),
                DB::raw('COALESCE(advance_salaries.released_amount, 0) AS advance_salary_taken'),
                'employee_accounts.salary_cycle as salary_cycle',
                'salary_group_employees.salary_group_id as salary_group_id',
                'salary_groups.name as salary_group_name',
                'employee_accounts.salary_cycle'
            )
            ->leftJoin('employee_accounts','users.id','employee_accounts.user_id')
            ->leftJoin('salary_group_employees','users.id','salary_group_employees.employee_id')
            ->leftJoin('salary_groups','salary_group_employees.salary_group_id','salary_groups.id')
            ->leftJoin('employee_salaries','users.id','employee_salaries.employee_id')
            ->leftJoin('advance_salaries',function($join) {
                $join->on('advance_salaries.employee_id', 'users.id')->where('advance_salaries.is_settled',false)->where('advance_salaries.status', '=', 'approved');
            })
            ->where('users.id', $employeeId)
            ->where('users.status', self::STATUS_VERIFIED)
            ->when(isset($filterData['salary_cycle']), function ($query) use ($filterData) {
                $query->where('employee_accounts.salary_cycle', $filterData['salary_cycle']);
            })->get();
    }


    public function getEmployeeSalaryDetailsToGeneratePayslip(): Collection|array
    {
        return User::query()

            ->select(
                'users.id as employee_id',
                'users.name as employee_name',
                'users.marital_status as marital_status',
                'employee_salaries.id as employee_salary_id',
                'employee_salaries.annual_salary as annual_salary',
                'employee_salaries.monthly_basic_salary as monthly_basic_salary',
                'employee_salaries.annual_basic_salary as annual_basic_salary',
                'employee_salaries.monthly_fixed_allowance as monthly_fixed_allowance',
                'employee_salaries.annual_fixed_allowance as annual_fixed_allowance',
                'employee_salaries.salary_group_id as salary_group_id',
                DB::raw('COALESCE(advance_salaries.is_settled, true) AS advance_settled'),
                DB::raw('COALESCE(advance_salaries.released_amount, 0) AS advance_salary_taken'),
            )
            ->leftJoin('employee_salaries','users.id','employee_salaries.employee_id')
            ->leftJoin('advance_salaries',function($join) {
                $join->on('advance_salaries.employee_id', 'users.id')->where('advance_salaries.is_settled',false)->where('advance_salaries.status', '=', 'approved');
            })
            ->leftJoin('salary_groups','employee_salaries.salary_group_id','salary_groups.id')
            ->join('posts','users.post_id','posts.id')
            ->whereNotNull('employee_salaries.employee_id')
            ->where('users.status',self::STATUS_VERIFIED)
            ->get();
    }

//     public function getEmployeeSalaryDetailsToGeneratePayslip()
//    {
//        return User::query()
//
//            ->select(
//                'users.id as employee_id',
//                'users.name as employee_name',
//                'users.avatar as employee_avatar',
//                'users.email as employee_email',
//                'users.joining_date as joining_date',
//                'posts.post_name as designation',
//                DB::raw('CAST(users.phone AS UNSIGNED) AS employee_phone'),
//
//                'users.marital_status as marital_status',
//                'employee_salaries.id as employee_salary_id',
//                'employee_salaries.annual_salary as annual_salary',
//                'employee_salaries.monthly_basic_salary as monthly_basic_salary',
//                'employee_salaries.annual_basic_salary as annual_basic_salary',
//                'employee_salaries.monthly_fixed_allowance as monthly_fixed_allowance',
//                'employee_salaries.annual_fixed_allowance as annual_fixed_allowance',
//                'employee_salaries.salary_group_id as salary_group_id',
//                'salary_groups.name  as salary_group_name',
//                DB::raw('COALESCE(advance_salaries.is_settled, true) AS advance_settled'),
//                DB::raw('COALESCE(advance_salaries.released_amount, 0) AS advance_salary_taken'),
//            )
//            ->leftJoin('employee_salaries','users.id','employee_salaries.employee_id')
//            ->leftJoin('advance_salaries',function($join) {
//                $join->on('advance_salaries.employee_id', 'users.id')->where('advance_salaries.is_settled',false);
//            })
//            ->leftJoin('salary_groups','employee_salaries.salary_group_id','salary_groups.id')
//            ->join('posts','users.post_id','posts.id')
//            ->whereNotNull('employee_salaries.employee_id')
//            ->where('users.status',self::STATUS_VERIFIED)
//            ->get();
//    }

    public function checkOfficeTime($officeTimeId)
    {
        return User::where('office_time_id',$officeTimeId)->where('status','=','verified')->count();
    }


    public function getAllActiveEmployeeOfDepartment($departmentId,$select = ['*'], $with=[])
    {
        return User::select($select)
            ->with($with)
            ->where('status', 'verified')
            ->where('is_active', self::IS_ACTIVE)
            ->where('department_id', $departmentId)
            ->orderBy('online_status','desc')
            ->where('id','!=', getAuthUserCode())
            ->get();
    }

    public function getActiveEmployeesByDepartment($departmentIds, $select = ['*'])
    {
        return User::select($select)
            ->where('status', 'verified')
            ->where('is_active', self::IS_ACTIVE)
            ->whereIn('department_id', $departmentIds)
            ->get();
    }

    public function getActiveEmployeesFromDepartments($departmentIds, $select = ['*'])
    {
        return User::select($select)
            ->where('status', 'verified')
            ->where('id','!=', getAuthUserCode())
            ->where('is_active', self::IS_ACTIVE)
            ->whereIn('department_id', $departmentIds)
            ->get();
    }

    public function getActiveEmployeesByDepartments($departmentIds, $select = ['*'])
    {
        return User::select($select)
            ->where('status', 'verified')
            ->where('is_active', self::IS_ACTIVE)
            ->whereIn('department_id', $departmentIds)
            ->get();
    }

    public function getBirthdayUsersByMonth($dates, array $with = [], array $select = ['*'])
    {

        $start = Carbon::parse($dates['start_date'])->format('m-d');
        $end   = Carbon::parse($dates['end_date'])->format('m-d');

        return User::query()
            ->select($select)
            ->with($with)
            ->where('is_active', self::IS_ACTIVE)
            ->whereNull('deleted_at')
            ->whereRaw("DATE_FORMAT(dob, '%m-%d') BETWEEN ? AND ?", [$start, $end])
            ->orderByRaw("MONTH(dob), DAY(dob)")->get();
    }

    public function getBirthdayUsers($date, $with=[],$select=['*'])
    {
        $dateFormatted = Carbon::parse($date)->format('m-d');

        return User::query()
            ->select($select)
            ->with($with)
            ->whereRaw("DATE_FORMAT(dob, '%m-%d') = ?", [$dateFormatted])
            ->where('is_active',self::IS_ACTIVE)
            ->whereNull('deleted_at')
            ->get();
    }


    public function deactivateUserAccount($id)
    {
        $userDetail = $this->findUserDetailById($id);

        $userDetail->tokens()->delete();

        return $userDetail->update([
            'is_active' => false,
            'logout_status' => true,
            'fcm_token' => null,
            'online_status' => false
        ]);
    }

    public function getActiveEmployeeOfDepartment($departmentId,$select = ['*'])
    {
        return User::select($select)
            ->where('status', 'verified')
            ->where('is_active', self::IS_ACTIVE)
            ->where('department_id', $departmentId)
            ->get();
    }

    public function getActiveEmployeeOfBranch($branchId,$select = ['*'])
    {
        return User::select($select)
            ->where('status', 'verified')
            ->where('is_active', self::IS_ACTIVE)
            ->where('branch_id', $branchId)
            ->get();
    }

    public function getAllActiveEmployeeByDepartment($departmentId,$select = ['*'], $with=[])
    {
        return User::select($select)
            ->with($with)
            ->where('status', 'verified')
            ->where('is_active', self::IS_ACTIVE)
            ->where('department_id', $departmentId)
            ->orderBy('online_status','desc')
            ->get();
    }


    public function getLocationLogs($filterData)
    {
        if(AppHelper::ifDateInBsEnabled()){
            $filterData['date'] = isset($filterData['date']) ?
                AppHelper::dateInYmdFormatNepToEng($filterData['date']): null;

        }
        $query = EmployeeLocation::with(['employee'])
            ->whereHas('employee', function ($query) {
                $query->where('is_active', 1);
            })
            ->when(isset($filterData['branch_id']), function ($query) use ($filterData) {
                $query->whereHas('employee', function ($query) use ($filterData) {
                    $query->where('branch_id', $filterData['branch_id']);
                });
            })
            ->when(isset($filterData['department_id']), function ($query) use ($filterData) {
                $query->whereHas('employee', function ($query) use ($filterData) {
                    $query->where('department_id', $filterData['department_id']);
                });
            })
            ->when(isset($filterData['employee_id']), function ($query) use ($filterData) {
                $query->where('employee_id', $filterData['employee_id']);
            })
            ->when(isset($filterData['date']), function ($query) use ($filterData) {
                $query->whereDate('created_at', $filterData['date']);
            });

        // Group by employee_id
        return $query->get()->groupBy('employee_id');
    }

    public function setEmployeeLocation($validatedData)
    {
        return EmployeeLocation::create($validatedData)->fresh();
    }

    public function getEmployeeForBonus($select = ['*'])
    {
        $limit = AppHelper::getBonusReceiveLimit();
        return User::select($select)
            ->where('status', 'verified')
            ->where('is_active', self::IS_ACTIVE)
            ->where('joining_date', '<=', now()->subMonths($limit))
            ->whereHas('employeeSalary', function ($query) {
                $query->whereNotNull('employee_id');
            })
            ->get();
    }

    public function getEmployeeByDepartmentForBonus($departmentIds,$select = ['*'])
    {
        $limit = AppHelper::getBonusReceiveLimit();
        return User::select($select)
            ->where('status', 'verified')
            ->where('is_active', self::IS_ACTIVE)
            ->where('joining_date', '<=', now()->subMonths($limit))
            ->whereIn('department_id', $departmentIds)
            ->whereHas('employeeSalary', function ($query) {
                $query->whereNotNull('employee_id');
            })
            ->get();
    }
}
