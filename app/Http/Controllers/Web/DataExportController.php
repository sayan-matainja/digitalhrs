<?php

namespace App\Http\Controllers\Web;

use App\Exports\DatabaseData\AttendanceCollectionExport;
use App\Exports\DatabaseData\EmployeeExport;
use App\Exports\DatabaseData\LeaveMasterExport;
use App\Exports\DatabaseData\LeaveTypeExport;
use App\Http\Controllers\Controller;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\UserRepository;
use Exception;
use Maatwebsite\Excel\Facades\Excel;

class DataExportController extends Controller
{
    public function __construct(
        private LeaveTypeRepository $leaveTypeRepo,
        private UserRepository      $userRepo,
    )
    {
    }

    public function exportLeaveType()
    {
        try {
            $leaveTypes = $this->leaveTypeRepo->exportLeaveTypes(['id', 'name', 'leave_allocated']);
            return Excel::download(new LeaveTypeExport($leaveTypes), 'leaveType.xlsx');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function exportEmployeeDetail()
    {
        try {
            $employeeDetail = $this->userRepo->getAllCompanyUsers(
                ['id', 'name', 'email', 'username', 'address', 'dob', 'gender', 'phone', 'status', 'role_id', 'employment_type', 'user_type', 'joining_date',
                    'branch_id', 'department_id', 'post_id'],
                ['branch:id,name', 'department:id,dept_name', 'post:id,post_name', 'role','accountDetail:id,bank_name,bank_account_no,bank_account_type'],
            );
//            'bank_name', 'bank_account_no', 'bank_account_type',
            return Excel::download(new EmployeeExport($employeeDetail), 'employeeDetail.xlsx');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function exportEmployeeLeaveRequestLists()
    {
        try {
            return Excel::download(new LeaveMasterExport, 'leaveRequest.xlsx');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function exportAttendanceDetail()
    {
        try {
            return Excel::download(new AttendanceCollectionExport, 'attendance.xlsx');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}
