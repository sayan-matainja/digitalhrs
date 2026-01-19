<?php

namespace App\Http\Controllers\Web;

use App\Enum\LeaveGenderEnum;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\LeaveRepository;
use App\Repositories\LeaveTypeRepository;
use App\Requests\Leave\LeaveTypeRequest;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LeaveTypeController extends Controller
{
    private $view = 'admin.leaveType.';


    public function __construct(protected LeaveTypeRepository $leaveTypeRepo,
                                protected LeaveRepository     $leaveRepo,
                                protected BranchRepository    $branchRepository,
                                protected CompanyRepository   $companyRepository
    )
    {
    }

    public function index(Request $request)
    {
        if (auth('admin')->user() || Gate::allows('list_leave_type') || Gate::allows('access_admin_leave')) {
            try {
                $filterParameters = [
                    'branch_id' => $request->branch_id ?? null,
                    'type' => $request->type ?? null,
                ];

                if (!auth('admin')->check() && auth()->check()) {
                    $filterParameters['branch_id'] = auth()->user()->branch_id;
                }

                $leaveTypes = $this->leaveTypeRepo->getAllLeaveTypes($filterParameters,['*'],['branch:id,name']);
                $with = ['branches:id,name'];
                $select = ['id', 'name'];
                $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
                $genders = LeaveGenderEnum::cases();
                return view($this->view . 'index', compact('leaveTypes','companyDetail','filterParameters','genders'));
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }
        } else {
            abort(403);
        }
    }

//    public function create()
//    {
//        if (auth('admin')->user() || Gate::allows('leave_type_create') || Gate::allows('access_admin_leave')) {
//            try {
//                $companyId = AppHelper::getAuthUserCompanyId();
//                $selectBranch = ['id', 'name'];
//                $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId, $selectBranch);
//                $genders = LeaveGenderEnum::cases();
//                return view($this->view . 'create', compact('branch','genders'));
//            } catch (Exception $exception) {
//                return redirect()->back()->with('danger', $exception->getMessage());
//            }
//        } else {
//            abort(403); // Unauthorized
//        }
//    }

    public function store(LeaveTypeRequest $request)
    {
        if (auth('admin')->user() || Gate::allows('leave_type_create') || Gate::allows('access_admin_leave')) {
            try {
                $validatedData = $request->validated();
                $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
                $this->leaveTypeRepo->store($validatedData);
                return redirect()
                    ->route('admin.leaves.index')
                    ->with('success', __('message.leave_type_added'));
            } catch (Exception $e) {
                return redirect()->back()
                    ->with('danger', $e->getMessage())
                    ->withInput();
            }
        } else {
            abort(403); // Unauthorized
        }
    }

    public function edit($id)
    {
        if (auth('admin')->user() || Gate::allows('leave_type_edit') || Gate::allows('access_admin_leave')) {

            try {

                $leaveTypeDetail = $this->leaveTypeRepo->findLeaveTypeDetailById($id);
                return response()->json([
                    'leaveTypeDetail' => $leaveTypeDetail,
                ]);
            } catch (Exception $exception) {
                return response()->json(['message' => $exception->getMessage()], 500);
            }
        } else {
            abort(403); // Unauthorized
        }
    }
    public function update(LeaveTypeRequest $request, $id)
    {
        if (auth('admin')->user() || Gate::allows('leave_type_edit') || Gate::allows('access_admin_leave')) {

            try {
                $validatedData = $request->validated();
                $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
                $leaveDetail = $this->leaveTypeRepo->findLeaveTypeDetailById($id);
                if (!$leaveDetail) {
                    throw new Exception(__('message.leave_type_not_found'), 404);
                }
                $this->leaveTypeRepo->update($leaveDetail, $validatedData);
                return redirect()
                    ->route('admin.leaves.index')
                    ->with('success', __('message.leave_type_updated'));
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage())
                    ->withInput();
            }
        } else {
            abort(403); // Unauthorized
        }

    }

    public function toggleStatus($id)
    {
        if (auth('admin')->user() || Gate::allows('leave_type_edit') || Gate::allows('access_admin_leave')) {
            try {
                $this->leaveTypeRepo->toggleStatus($id);
                return redirect()->back()->with('success', __('message.status_changed'));
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }
        } else {
            abort(403); // Unauthorized
        }
    }

    public function toggleEarlyExit($id)
    {
        if (auth('admin')->user() || Gate::allows('leave_type_edit') || Gate::allows('access_admin_leave')) {

            try {
                $this->leaveTypeRepo->toggleEarlyExitStatus($id);
                return redirect()->back()->with('success', __('message.leave_type_early_exit_status_changed'));
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }
        } else {
            abort(403); // Unauthorized
        }
    }

    public function delete($id)
    {
        if (auth('admin')->user() || Gate::allows('leave_type_delete') || Gate::allows('access_admin_leave')) {
            try {
                $leaveType = $this->leaveTypeRepo->findLeaveTypeDetailById($id);
                if (!$leaveType) {
                    throw new Exception(__('message.leave_type_not_found'), 404);
                }
                $checkLeaveTypeIfUsed = $this->leaveRepo->findLeaveRequestCountByLeaveTypeId($leaveType->id);
                if ($checkLeaveTypeIfUsed > 0) {
                    throw new Exception(__('message.leave_type_cannot_delete_in_use', ['name' => ucfirst($leaveType->name)]), 402);
                }
                $this->leaveTypeRepo->delete($leaveType);
                return redirect()->back()->with('success', __('message.leave_type_deleted'));
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }
        } else {
            abort(403); // Unauthorized
        }
    }

    public function getLeaveTypesBasedOnEarlyExitStatus($status)
    {
        try {
            $leaveType = $this->leaveTypeRepo->getAllLeaveTypesBasedOnEarlyExitStatus($status);
            return AppHelper::sendSuccessResponse(__('message.data_found'), $leaveType);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getGenderSpecificLeaveTypes($branchId,$gender)
    {
        try {
            $leaveType = $this->leaveTypeRepo->getGenderSpecificPaidLeaveTypes($branchId,$gender);
            return response()->json([
                'leaveTypes' => $leaveType,
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getEmployeeLeaveTypes($employeeId)
    {
        try {
            $leaveType = $this->leaveTypeRepo->getEmployeePaidLeaveTypes($employeeId);
            return response()->json([
                'leveTypes' => $leaveType,
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }
}
