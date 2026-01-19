<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    public function getCompanyDashboardDetail($companyId, $date)
    {
        $currentDate = AppHelper::getCurrentDateInYmdFormat();
        $branchId = null;
        if(auth()->user()){
            $branchId = auth()->user()->branch_id;
        }

        $totalCompanyEmployee = DB::table('users')
            ->select('company_id', DB::raw('COUNT(id) as total_employee'))
            ->whereNull('deleted_at')
            ->where('status', 'verified')
            ->where('is_active', 1)
            ->when(isset($branchId), function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->groupBy('company_id');

        $totalDepartments = DB::table('departments')
            ->select('company_id', DB::raw('COUNT(id) as total_departments'))
            ->where('is_active', 1)
            ->when(isset($branchId), function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->groupBy('company_id');

        $totalCheckedInEmployee = DB::table('attendances')
            ->select('attendances.company_id', DB::raw('COUNT(attendances.id) as total_checked_in_employee'))
            ->leftJoin('users','attendances.user_id','users.id')
            ->whereDate('attendances.attendance_date', $currentDate)
            ->whereNotNull('attendances.check_in_at')
            ->when(isset($branchId), function ($query) use ($branchId) {
                $query->where('users.branch_id', $branchId);
            })
            ->groupBy('attendances.company_id');

        $totalCheckedOutEmployee = DB::table('attendances')
            ->select('attendances.company_id', DB::raw('COUNT(attendances.id) as total_checked_out_employee'))
            ->leftJoin('users','attendances.user_id','users.id')

            ->whereDate('attendances.attendance_date', $currentDate)
            ->whereNotNull('attendances.check_in_at')
            ->whereNotNull('attendances.check_out_at')
            ->when(isset($branchId), function ($query) use ($branchId) {
                $query->where('users.branch_id', $branchId);
            })
            ->groupBy('attendances.company_id');

        $onLeaveEmployee = DB::table('leave_requests_master')
            ->select('leave_requests_master.company_id', DB::raw('count(leave_requests_master.id) as total_on_leave'))
            ->leftJoin('users','leave_requests_master.requested_by','users.id')

            ->whereDate('leave_requests_master.leave_from', '<=', $currentDate)
            ->whereDate('leave_requests_master.leave_to', '>=', $currentDate)
            ->where('leave_requests_master.status', 'approved')
            ->when(isset($branchId), function ($query) use ($branchId) {
                $query->where('users.branch_id', $branchId);
            })
            ->groupBy('leave_requests_master.company_id');

        $pendingLeavesRequests = DB::table('leave_requests_master')
            ->select('leave_requests_master.company_id', DB::raw('count(leave_requests_master.id) as total_pending_leave_requests'))
            ->leftJoin('users', 'leave_requests_master.requested_by', '=', 'users.id') // Move join here
            ->where('leave_requests_master.status', 'pending');
            if (isset($date['start_date'])) {
                $pendingLeavesRequests->whereBetween('leave_requests_master.leave_requested_date', [$date['start_date'], $date['end_date']]);
            } else {
                $pendingLeavesRequests->whereYear('leave_requests_master.leave_requested_date', $date['year']);
            }
            $pendingLeavesRequests->when($branchId, function ($query) use ($branchId) {
                return $query->where('users.branch_id', $branchId);
            });
            $pendingLeavesRequests->groupBy('leave_requests_master.company_id');



        $companyPaidLeaves = DB::table('leave_types')
            ->select('company_id', DB::raw('sum(leave_allocated) as total_paid_leaves'))
            ->whereNotNull('leave_allocated')
            ->where('is_active', '1')
            ->groupBy('company_id');

        $totalHolidaysInYear = DB::table('holidays')
            ->select('company_id', DB::raw('count(id) as total_holidays'))
            ->where('is_active', '1');
        if (isset($date['start_date'])) {
            $totalHolidaysInYear->whereBetween('event_date', [$date['start_date'], $date['end_date']]);
        } else {
            $totalHolidaysInYear->whereYear('event_date', $date['year']);
        }
        $totalHolidaysInYear->groupBy('company_id');


        $projects = DB::table('projects')
            ->select(
                DB::raw('COALESCE(users.company_id, 1) as company_id'),
                DB::raw('count(projects.id) as total_projects')
            )
            ->leftJoin('users', function ($join) {
                $join->on('projects.created_by', '=', 'users.id');
            })
            ->when(isset($branchId), function ($query) use ($branchId) {
                $query->where('projects.branch_id', $branchId);
            })
            ->groupBy(DB::raw('COALESCE(users.company_id, 1)'));


        return DB::table('companies')->select(
            'companies.name as company_name',
            'company_employee.total_employee',
            'checked_in_employee.total_checked_in_employee',
            'checked_out_employee.total_checked_out_employee',
            'holidays.total_holidays',
            'on_leave_today.total_on_leave',
            'paid_leaves.total_paid_leaves',
            'pending_leave_requests.total_pending_leave_requests',
            'departments.total_departments',
            'projects.total_projects'
        )
            ->leftJoinSub($totalCompanyEmployee, 'company_employee', function ($join) {
                $join->on('companies.id', '=', 'company_employee.company_id');
            })

            ->leftJoinSub($totalDepartments, 'departments', function ($join) {
                $join->on('companies.id', '=', 'departments.company_id');
            })
            ->leftJoinSub($totalCheckedInEmployee, 'checked_in_employee', function ($join) {
                $join->on('companies.id', '=', 'checked_in_employee.company_id');
            })
            ->leftJoinSub($totalCheckedOutEmployee, 'checked_out_employee', function ($join) {
                $join->on('companies.id', '=', 'checked_out_employee.company_id');
            })
            ->leftJoinSub($totalHolidaysInYear, 'holidays', function ($join) {
                $join->on('companies.id', '=', 'holidays.company_id');
            })
            ->leftJoinSub($onLeaveEmployee, 'on_leave_today', function ($join) {
                $join->on('companies.id', '=', 'on_leave_today.company_id');
            })
            ->leftJoinSub($companyPaidLeaves, 'paid_leaves', function ($join) {
                $join->on('companies.id', '=', 'paid_leaves.company_id');
            })
            ->leftJoinSub($pendingLeavesRequests, 'pending_leave_requests', function ($join) {
                $join->on('companies.id', '=', 'pending_leave_requests.company_id');
            })
            ->leftJoinSub($projects, 'projects', function ($join) {
                $join->on('companies.id', '=', 'projects.company_id');
            })
            ->where('companies.is_active', 1)
            ->where('companies.id', $companyId)
            ->first();

    }

}


