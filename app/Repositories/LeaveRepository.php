<?php

namespace App\Repositories;

use App\Enum\LeaveStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Models\Admin;
use App\Models\LeaveApproval;
use App\Models\LeaveApprovalProcess;
use App\Models\LeaveRequestApproval;
use App\Models\LeaveRequestMaster;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveRepository
{
//    public function getAllEmployeeLeaveRequest($filterParameters, $select = ['*'], $with = [])
//    {
//        $leaveDetailList = LeaveRequestMaster::with($with)
//            ->select($select)
//            ->when(isset($filterParameters['requested_by']), function ($query) use ($filterParameters) {
//                $query->where('requested_by', $filterParameters['requested_by']);
//            })
//            ->when(isset($filterParameters['leave_type']), function ($query) use ($filterParameters) {
//                $query->where('leave_type_id', $filterParameters['leave_type']);
//            })
//            ->when(isset($filterParameters['branch_id']), function ($query) use ($filterParameters) {
//                $query->where('branch_id', $filterParameters['branch_id']);
//            })
//            ->when(isset($filterParameters['department_id']), function ($query) use ($filterParameters) {
//                $query->where('department_id', $filterParameters['department_id']);
//            })
//            ->when(isset($filterParameters['status']), function ($query) use ($filterParameters) {
//                $query->where('status', $filterParameters['status']);
//            });
//
//        if (isset($filterParameters['start_date'])) {
//            $startDate = $filterParameters['start_date'];
//            $endDate = $filterParameters['end_date'];
//
//
//            $leaveDetailList->where(function ($query) use ($startDate, $endDate) {
//                $query->whereBetween('leave_from', [$startDate, $endDate])
//                    ->orWhere('leave_from', '<=', $endDate);
//            })
//
//                ->where(function ($query) use ($startDate, $endDate) {
//                    $query->where(function ($q) use ($startDate, $endDate) {
//                        $q->whereBetween('leave_to', [$startDate, $endDate])
//                            ->orWhereNull('leave_to');
//                    })
//                        ->orWhere('leave_to', '>=', $startDate);
//                });
//        } else {
//            $leaveDetailList
//                ->when(isset($filterParameters['month']), function ($query) use ($filterParameters) {
//                    $month = $filterParameters['month'];
//                    $query->where(function ($q) use ($month) {
//                        $q->whereMonth('leave_from', '=', $month)
//                            ->orWhereMonth('leave_to', '=', $month)
//                            ->orWhereNull('leave_to');
//                    });
//                })
//                ->when(isset($filterParameters['year']), function ($query) use ($filterParameters) {
//                    $query->whereYear('leave_from', '=', $filterParameters['year'])
//                        ->orWhere(function ($q) use ($filterParameters) {
//                            $q->whereYear('leave_to', '=', $filterParameters['year'])
//                                ->orWhereNull('leave_to');
//                        });
//                });
//        }
//
//        return $leaveDetailList
//            ->orderBy('updated_at', 'DESC')
//            ->paginate(getRecordPerPage());
//    }


    public function getAllEmployeeLeaveRequest($filterParameters, $select = ['*'], $with = [])
    {
        $user   = auth()->user();
        $isAdmin = auth('admin')->check();

        $query = LeaveRequestMaster::with($with)->select($select);

        $query->when(isset($filterParameters['requested_by']), fn($q) => $q->where('requested_by', $filterParameters['requested_by']))
            ->when(isset($filterParameters['leave_type']),   fn($q) => $q->where('leave_type_id', $filterParameters['leave_type']))
            ->when(isset($filterParameters['branch_id']),    fn($q) => $q->where('branch_id', $filterParameters['branch_id']))
            ->when(isset($filterParameters['department_id']),fn($q) => $q->where('department_id', $filterParameters['department_id']))
            ->when(isset($filterParameters['status']),       fn($q) => $q->where('status', $filterParameters['status']));

        if (isset($filterParameters['start_date'])) {
            $startDate = $filterParameters['start_date'];
            $endDate   = $filterParameters['end_date'];

            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('leave_from', [$startDate, $endDate])
                    ->orWhere('leave_from', '<=', $endDate);
            })->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($qq) use ($startDate, $endDate) {
                    $qq->whereBetween('leave_to', [$startDate, $endDate])
                        ->orWhereNull('leave_to');
                })->orWhere('leave_to', '>=', $startDate);
            });
        } else {
            $query->when(isset($filterParameters['month']), function ($q) use ($filterParameters) {
                $month = $filterParameters['month'];
                $q->whereMonth('leave_from', $month)
                    ->orWhereMonth('leave_to', $month)
                    ->orWhereNull('leave_to');
            })->when(isset($filterParameters['year']), function ($q) use ($filterParameters) {
                $year = $filterParameters['year'];
                $q->whereYear('leave_from', $year)
                    ->orWhereYear('leave_to', $year)
                    ->orWhereNull('leave_to');
            });
        }

        if (!$isAdmin) {

            // Roles that have full admin leave access
            $adminLeaveRoles = AppHelper::getRoleByPermission('access_admin_leave');
            $hasFullAdminAccess = $user && in_array($user->role_id, $adminLeaveRoles);

            $query->where(function ($q) use ($user, $hasFullAdminAccess) {
                // 2. Leaves I have already approved/rejected
                $q->orWhereHas('requestApproval', fn($qa) => $qa->where('approved_by', $user->id));

                // 3. Pending leaves where I am the NEXT approver
                //    We fetch them in PHP because getNextApprover is complex
                $pendingIdsWhereIAmNextApprover = $this->getPendingLeaveIdsWhereUserIsNextApprover($user);

                if ($pendingIdsWhereIAmNextApprover->isNotEmpty()) {
                    $q->orWhereIn('id', $pendingIdsWhereIAmNextApprover);
                }

                // 4. Users with "access_admin_leave" permission see everything
                if ($hasFullAdminAccess) {
                    $q->orWhereRaw('1 = 1'); // makes the whole group true → see all
                }
            });
        }

        return $query->orderBy('updated_at', 'DESC')
            ->paginate(getRecordPerPage());
    }

    private function getPendingLeaveIdsWhereUserIsNextApprover($user)
    {
        if (!$user) return collect();

        // Get only pending requests (reduces the set dramatically)
        $pendingRequests = LeaveRequestMaster::where('status', 'pending')
            ->select('id', 'leave_type_id', 'requested_by')
            ->get();

        $ids = collect();

        foreach ($pendingRequests as $req) {
            $nextApproverId = AppHelper::getNextApprover($req->id, $req->leave_type_id, $req->requested_by);
            if ($nextApproverId == $user->id) {
                $ids->push($req->id);
            }
        }

        return $ids;
    }


    public function getAllLeaveRequestDetailOfEmployee($filterParameters)
    {
        $leaveDetailList = LeaveRequestMaster::select(
            'leave_requests_master.id',
            'leave_requests_master.leave_from',
            'leave_requests_master.leave_to',
            'leave_requests_master.leave_for',
            'leave_requests_master.leave_in',
            'leave_requests_master.no_of_days',
            'leave_requests_master.leave_type_id',
            'leave_requests_master.leave_requested_date',
            'leave_requests_master.status',
            'leave_requests_master.reasons as leave_reason',
            'leave_requests_master.admin_remark',
            'leave_requests_master.early_exit',
            'leave_requests_master.request_updated_by',
            'leave_requests_master.requested_by',
            'leave_types.name as leave_type_name',
        )
            ->join('leave_types', 'leave_types.id', '=', 'leave_requests_master.leave_type_id')
            ->when(isset($filterParameters['leave_type']), function ($query) use ($filterParameters) {
                $query->where('leave_requests_master.leave_type_id', $filterParameters['leave_type']);
            })
            ->when(isset($filterParameters['status']), function ($query) use ($filterParameters) {
                $query->where('leave_requests_master.status', $filterParameters['status']);
            })
            ->when(isset($filterParameters['early_exit']), function ($query) use ($filterParameters) {
                $query->where('leave_requests_master.early_exit', $filterParameters['early_exit']);
            })
            ->where('leave_requests_master.requested_by', $filterParameters['user_id']);

                $startDate = $filterParameters['start_date'];
                $endDate   = $filterParameters['end_date'];

                $leaveDetailList->where(function ($query) use ($startDate, $endDate) {
                    $query->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->whereNotNull('leave_requests_master.leave_to')
                            ->where('leave_requests_master.leave_from', '<=', $endDate)
                            ->where('leave_requests_master.leave_to', '>=', $startDate);
                    })

                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->whereNull('leave_requests_master.leave_to')
                                ->whereBetween('leave_requests_master.leave_from', [$startDate, $endDate]);
                        });
                });

//
//            else {
//                $leaveDetailList->when(!empty($filterParameters['month']), function ($q) use ($filterParameters) {
//                    $month = $filterParameters['month'];
//
//                    $q->where(function ($query) use ($month) {
//                        $query->whereNotNull('leave_requests_master.leave_to')
//                            ->whereMonth('leave_requests_master.leave_from', $month)
//                            ->orWhereMonth('leave_requests_master.leave_to', $month);
//                    })->orWhere(function ($query) use ($month) {
//                        $query->whereNull('leave_requests_master.leave_to')
//                            ->whereMonth('leave_requests_master.leave_from', $month);
//                    });
//                })
//
//                    ->when(!empty($filterParameters['year']), function ($q) use ($filterParameters) {
//                        $year = $filterParameters['year'];
//
//                        $q->where(function ($query) use ($year) {
//                            $query->whereNotNull('leave_requests_master.leave_to')
//                                ->whereYear('leave_requests_master.leave_from', $year)
//                                ->orWhereYear('leave_requests_master.leave_to', $year);
//                        })->orWhere(function ($query) use ($year) {
//                            $query->whereNull('leave_requests_master.leave_to')
//                                ->whereYear('leave_requests_master.leave_from', $year);
//                        });
//                    });
//            }

        return $leaveDetailList->orderBy('leave_requests_master.id', 'DESC')
            ->get();
    }

    public function findEmployeeLeaveRequestByEmployeeId($leaveRequestId, $select = ['*'], $with = [])
    {
        return LeaveRequestMaster::with($with)
            ->select($select)
            ->where('id', $leaveRequestId)
            ->first();
    }
    public function findEmployeeLeaveRequestReasonById($leaveRequestId)
    {
        return LeaveRequestMaster::select('leave_requests_master.reasons', 'leave_requests_master.admin_remark','users.name')
            ->leftJoin('users','leave_requests_master.referred_by','=','users.id')
            ->where('leave_requests_master.id', $leaveRequestId)
            ->first();
    }

    public function employeeTotalApprovedLeavesForGivenLeaveType($leaveType, $date)
    {
        return LeaveRequestMaster::where('requested_by', getAuthUserCode())
            ->where('status', 'approved')
            ->where('leave_type_id', $leaveType)
            ->where(function ($query) use ($date) {
                $query->orWhere(function ($q) use ($date) {
                    $q->whereNotNull('leave_requests_master.leave_to')
                        ->where('leave_requests_master.leave_from', '<=', $date['end_date'])
                        ->where('leave_requests_master.leave_to', '>=', $date['start_date']);
                })

                    ->orWhere(function ($q) use ($date) {
                        $q->whereNull('leave_requests_master.leave_to')
                            ->whereBetween('leave_requests_master.leave_from', [$date['start_date'], $date['end_date']]);
                    });
            })
            ->sum('no_of_days');
    }

    public function getEmployeeLatestLeaveRequestBetweenFromAndToDate($data,$select = ['*'])
    {
        return LeaveRequestMaster::query()
            ->select($select)
            ->where(function ($query) use ($data) {
                $query->orWhere(function ($q) use ($data) {
                    $q->whereNotNull('leave_requests_master.leave_to')
                        ->where('leave_requests_master.leave_from', '<=', $data['leave_to'])
                        ->where('leave_requests_master.leave_to', '>=', $data['leave_from']);
                })

                    ->orWhere(function ($q) use ($data) {
                        $q->whereNull('leave_requests_master.leave_to')
                            ->whereBetween('leave_requests_master.leave_from', [$data['leave_from'], $data['leave_to']]);
                    });
            })
            ->whereIn('status', ['pending', 'approved'])
            ->where('requested_by', $data['requested_by'])
            ->first();
    }

    public function getMonthLeaveRequestList($data) {
        $leaves = LeaveRequestMaster::query()
            ->where('leave_from', '<=', $data['end_date'])
            ->where(function ($q) use ($data) {
                $q->whereNull('leave_to')
                    ->orWhere('leave_to', '>=', $data['start_date']);
            })
            ->whereIn('status', ['approved'])
            ->where('requested_by', $data['user_id'])
            ->get();

        $dateList = [];
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);

        foreach ($leaves as $leave) {
            $leaveFrom = Carbon::parse($leave->leave_from);
            $leaveTo = Carbon::parse($leave->leave_to);
            $period = CarbonPeriod::create($leaveFrom, $leaveTo);

            foreach ($period as $date) {
                if ($date->between($start, $end)) {
                    $dateList[] = $date->toDateString();
                }
            }
        }

        return collect($dateList)
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    public function getAnnualLeaveRequestSummary($startDate, $endDate,$filterData)
    {
        return User::select('id', 'name')
            ->with(['employeeAttendance' => function ($query) use ($startDate, $endDate) {
                $query->where('attendance_status', 1)
                    ->whereBetween('attendance_date', [$startDate, $endDate]);
            }])
            ->withCount(['employeeAttendance as present_days' => function ($query) use ($startDate, $endDate) {
                $query->where('attendance_status', 1)
                    ->whereBetween('attendance_date', [$startDate, $endDate])
                    ->selectRaw('COUNT(DISTINCT attendance_date)');
            }])
            ->when(isset($filterData['branch_id']), fn($q) => $q->where('branch_id', $filterData['branch_id']))
            ->when(isset($filterData['department_id']), fn($q) => $q->where('department_id', $filterData['department_id']))
            ->when(isset($filterData['employee_id']), fn($q) => $q->where('id', $filterData['employee_id']))
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();


    }



    public function getEmployeetPendingLeaveRequest($data,$select = ['*'])
    {

        return LeaveRequestMaster::query()
            ->select($select)
            ->whereDate('leave_from', '>=', now()->toDateString())
            ->whereIn('status', ['pending'])
            ->where('requested_by', $data['requested_by'])
            ->first();
    }
    public function getEmployeeLatestLeaveRequestDate($date)
    {

        return LeaveRequestMaster::query()
            ->where(function ($query) use ($date) {
                $query->whereDate('leave_requests_master.leave_from', '<=', $date)
                    ->where(function ($q) use ($date) {
                        $q->whereNull('leave_requests_master.leave_to')
                            ->orWhereDate('leave_requests_master.leave_to', '>=', $date);
                    });
            })
            ->whereIn('status', ['pending', 'approved'])
            ->where('requested_by', getAuthUserCode())
            ->first();
    }

    public function store($validatedData)
    {
        return LeaveRequestMaster::create($validatedData)->fresh();
    }

    public function update($leaveRequestDetail, $validatedData)
    {
        return $leaveRequestDetail->update($validatedData);
    }

    public function findLeaveRequestCountByLeaveTypeId($leaveTypeId)
    {
        return LeaveRequestMaster::select('id')->where('leave_type_id', $leaveTypeId)->count();
    }

    public function getLeaveCountDetailOfEmployeeOfTwoMonth()
    {
        $date = AppHelper::getStartEndDateForLeaveCalendar();

        return LeaveRequestMaster::select('no_of_days', 'leave_from')
            ->whereHas('leaveRequestedBy', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->where('status', 'approved')
            ->where(function ($query) use ($date) {
                $query->whereBetween('leave_requests_master.leave_from', [$date['start_date'], $date['end_date']])
                    ->orWhereBetween('leave_requests_master.leave_to', [$date['start_date'], $date['end_date']])
                    ->orWhere(function ($q) use ($date) {
                        $q->whereNull('leave_requests_master.leave_to')
                            ->whereBetween('leave_requests_master.leave_from', [$date['start_date'], $date['end_date']]);
                    });
            })
            ->orderBy('leave_from')
            ->get();
    }

    public function getAllEmployeeLeaveDetailBySpecificDay($filterParameter)
    {


        $date = AppHelper::getStartEndDateForLeaveCalendar();
        return  LeaveRequestMaster::select(
            'leave_requests_master.id as leave_id',
            'users.id as user_id',
            'users.name as name',
            'users.avatar as avatar',
            'departments.dept_name as department',
            'posts.post_name as post',
            'leave_requests_master.no_of_days as no_of_days',
            'leave_requests_master.leave_from as leave_from',
            'leave_requests_master.leave_to as leave_to',
            'leave_requests_master.status as leave_status'
        )
            ->Join('users', function ($join) {
                $join->on('leave_requests_master.requested_by', '=', 'users.id')
                    ->whereNUll('users.deleted_at');
            })
            ->join('departments', 'departments.id', '=', 'users.department_id')
            ->join('posts', 'posts.id', '=', 'users.post_id')
            ->where(function ($query) use ($filterParameter, $date) {
                $query->whereDate('leave_requests_master.leave_from', '<=', $filterParameter['leave_date'])
                    ->where(function ($q) use ($filterParameter) {
                        $q->whereNull('leave_requests_master.leave_to')
                            ->orWhereDate('leave_requests_master.leave_to', '>=', $filterParameter['leave_date']);
                    });
            })

            ->where(function ($query) use ($date) {
                $query->whereBetween('leave_requests_master.leave_from', [$date['start_date'], $date['end_date']])
                    ->orWhereBetween('leave_requests_master.leave_to', [$date['start_date'], $date['end_date']])
                    ->orWhere(function ($q) use ($date) {
                        $q->whereNull('leave_requests_master.leave_to')
                            ->whereBetween('leave_requests_master.leave_from', [$date['start_date'], $date['end_date']]);
                    });
            })

        ->where('leave_requests_master.status', 'approved')
            ->orderBy('leave_requests_master.leave_from')
            ->get();

    }

    public function getAllEmployeeLeaveDetail($filterParameter)
    {
        return LeaveRequestMaster::select([
            'leave_requests_master.id as leave_id',
            'users.id as user_id',
            'users.name',
            'users.avatar',
            'departments.dept_name as department',
            'posts.post_name as post',
            'leave_requests_master.no_of_days',
            'leave_requests_master.leave_from',
            'leave_requests_master.leave_to',
            'leave_requests_master.status as leave_status',
            'leave_requests_master.leave_requested_date',
            'leave_types.name as leave_title',
        ])
            ->join('users', 'users.id', '=', 'leave_requests_master.requested_by')
            ->whereNull('users.deleted_at')
            ->join('departments', 'departments.id', '=', 'users.department_id')
            ->join('posts', 'posts.id', '=', 'users.post_id')
            ->join('leave_types', 'leave_types.id', '=', 'leave_requests_master.leave_type_id')
            ->where('leave_requests_master.status', 'approved')
            ->where('leave_requests_master.leave_from', '<=', $filterParameter['end_date'])
            ->where(function ($query) use ($filterParameter) {
                $query->whereNull('leave_requests_master.leave_to')
                    ->orWhere('leave_requests_master.leave_to', '>=', $filterParameter['start_date']);
            })
            ->orderBy('leave_requests_master.leave_from')
            ->get();
    }


    public function findEmployeeApprovedLeaveForCurrentDate($filterData, $select = ['*'])
    {

        return LeaveRequestMaster::select($select)
            ->where('leave_requests_master.leave_from', '<=', AppHelper::getCurrentDateInYmdFormat())
            ->where(function ($query)  {
                $query->whereNull('leave_requests_master.leave_to')
                    ->orWhere('leave_requests_master.leave_to', '>=', AppHelper::getCurrentDateInYmdFormat());
            })
            ->whereIn('status', ['approved','pending'])
            ->where('company_id', $filterData['company_id'])
            ->where('requested_by', $filterData['user_id'])
            ->first();
    }

    public function findEmployeeLeaveRequestDetailById($leaveRequestId,$employeeId,$select=['*'])
    {
        return LeaveRequestMaster::query()
            ->select($select)
            ->where('id', $leaveRequestId)
            ->where('requested_by', $employeeId)
            ->first();
    }


    public function getApprovalHistory($leaveRequestDetail)
    {

        if ($leaveRequestDetail->status === 'cancelled') {
            return collect([]);
        }

        $history = [];
        $step = 1;

        $leaveApproval = LeaveApproval::where('leave_type_id', $leaveRequestDetail->leave_type_id)->first();
        if (!$leaveApproval) {
            return collect([]);
        }

        $approvalSequence = LeaveApprovalProcess::where('leave_approval_id', $leaveApproval->id)
            ->orderBy('id')
            ->with(['user', 'role'])
            ->get();

        if ($approvalSequence->isEmpty()) {
            return collect([]);
        }

        $recordedApprovals = LeaveRequestApproval::where('leave_request_id', $leaveRequestDetail->id)
            ->with('approvedBy')
            ->get();

        $hasRecorded = $recordedApprovals->isNotEmpty();
        $recordedMap = $recordedApprovals->keyBy('approved_by');

        $requester = User::with(['supervisor', 'department.departmentHead'])
            ->find($leaveRequestDetail->requested_by);

        if (!$requester) {
            return collect([]);
        }

        if (in_array($leaveRequestDetail->status, ['rejected', 'approved'])) {
            if (!$hasRecorded) {
                return collect([]);
            }
        }

        $usedApproverIds = [];

        foreach ($approvalSequence as $process) {
            $approver = $this->resolveApprover($process, $requester);

            if (!$approver) {
                continue;
            }

            $approverId = $approver->id;

            if (in_array($approverId, $usedApproverIds)) {
                continue;
            }
            $usedApproverIds[] = $approverId;

            $recorded = $recordedMap->get($approverId);

            $status = $recorded
                ? ($recorded->status == 1 ? 'Approved' : 'Rejected')
                : 'Pending';

            $note = $recorded?->reason ?? '';
            $approvedAt = $recorded?->created_at ?? null;


            if ($leaveRequestDetail->status === 'pending' && !$recorded) {
                $nextApproved = false;
                foreach ($approvalSequence as $later) {
                    if ($later->id <= $process->id) continue;
                    $nextApprover = $this->resolveApprover($later, $requester);
                    if ($nextApprover && $recordedMap->has($nextApprover->id)) {
                        $nextApproved = true;
                        break;
                    }
                }
                if ($nextApproved) continue;
            }

            $history[] = [
                'step'          => $step++,
                'status'        => $status,
                'employee_code' => $approver->employee_code ?? '000000',
                'approver_name' => $approver->name ?? 'Unknown User',
                'role'          => $this->getRoleName($process, $approver),
                'note'          => $note,
                'approved_at'   => $approvedAt,
            ];
        }

        return collect($history);
    }

    private function resolveApprover($process, $requester)
    {
        return match ($process->approver) {
            'supervisor'        => $requester->supervisor,
            'department_head'   => $requester->department?->departmentHead,
            'specific_personnel' => $process->user,
        };
    }

    private function getRoleName($process, $approver)
    {
        return match ($process->approver) {
            'supervisor'        => 'Supervisor',
            'department_head'   => 'Department Head',
            default             => $process->role?->name ?? ($approver->role?->name ?? 'Head Of Division'),
        };
    }

    public function getEmployeeLeaveRequestByDate($employeeId,$date,$select=['*'])
    {

        return LeaveRequestMaster::select($select)
            ->where(function ($query) use ($date) {
                $query->whereDate('leave_from', '<=', $date)
                    ->where(function ($q) use ($date) {
                        $q->whereNull('leave_to')
                            ->orWhereDate('leave_to', '>=', $date);
                    });
            })
            ->whereIn('status', ['pending', 'approved'])
            ->where('requested_by', $employeeId)
            ->exists();
    }

}
