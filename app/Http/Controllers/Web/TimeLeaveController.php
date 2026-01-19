<?php

namespace App\Http\Controllers\Web;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Enum\LeaveStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\OfficeTime;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use App\Requests\Leave\TimeLeaveStoreRequest;
use App\Services\Attendance\AttendanceService;
use App\Services\Leave\TimeLeaveService;
use App\Services\Notification\NotificationService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TimeLeaveController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.timeLeaveRequest.';

    public function __construct(protected TimeLeaveService $timeLeaveService, protected NotificationService $notificationService,
                                protected AttendanceService $attendanceService, protected UserRepository $userRepository, protected CompanyRepository $companyRepository){}

    public function index(Request $request)
    {
        $this->authorize('time_leave_list');
        try {
            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'requested_by' => $request->requested_by ?? null,
                'month' => $request->month ?? null,
                'year' => $request->year ?? Carbon::now()->format('Y'),
                'status' => $request->status ?? null
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            if(AppHelper::ifDateInBsEnabled()){
                $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
                $filterParameters['year'] = $request->year ?? $nepaliDate['year'];
            }
            $months = AppHelper::MONTHS;
            $with = ['leaveRequestedBy:id,name'];
            $select = ['time_leaves.*'];
            $timeLeaves = $this->timeLeaveService->getAllEmployeeLeaveRequests($filterParameters, $select, $with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);

            return view($this->view . 'index',
                compact( 'timeLeaves','filterParameters','months','companyDetail') );
         } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function show($leaveId)
    {
        $this->authorize('time_leave_list');

        try {
            $leaveRequest = $this->timeLeaveService->findTimeLeaveRequestReasonById($leaveId);

            if (!$leaveRequest) {
                return response()->json(['error' => __('message.time_leave_not_found')], 404);
            }


            $leaveRequest->reasons = strip_tags($leaveRequest->reasons);

            return response()->json([
                'data' => $leaveRequest,
            ]);
        } catch (Exception $exception) {
            return response()->json(['error' => __('message.time_leve_fetch_error')], 500);
        }
    }

    public function updateLeaveRequestStatus(Request $request, $leaveRequestId)
    {
        $this->authorize('update_time_leave');

        try {
            $validatedData = $request->validate([
                'status' => ['required', 'string', Rule::in(array_column(LeaveStatusEnum::cases(), 'value'))],
                'admin_remark' => ['nullable', 'required_if:status,'.LeaveStatusEnum::rejected->value, 'string', 'min:10'],
            ]);

            DB::beginTransaction();

            $leaveRequestDetail = $this->timeLeaveService->updateLeaveRequestStatus($validatedData, $leaveRequestId);

            if ($leaveRequestDetail) {
                $notificationData = [
                    'title' => 'Time Leave Status Update',
                    'type' => 'leave',
                    'user_id' => [$leaveRequestDetail->requested_by],
                    'description' => 'Your Time Leave request requested on ' . date('M d Y', strtotime($leaveRequestDetail->issue_date)) . ' has been ' . ucfirst($validatedData['status']),
                    'notification_for_id' => $leaveRequestId,
                ];

                $notification = $this->notificationService->store($notificationData);

                if($notification){
                    $this->sendLeaveStatusNotification($notification,$leaveRequestDetail->requested_by);
                }

                // make checkout
                $attendanceData = $this->attendanceService->findEmployeeTodayAttendanceDetail($leaveRequestDetail->requested_by);

                if($attendanceData){
                    $user = $this->userRepository->findUserDetailById($attendanceData->user_id,['id','office_time_id']);

                    if((strtotime($attendanceData->attendance_date) == strtotime($leaveRequestDetail->issue_date)) && ($validatedData['status'] == LeaveStatusEnum::approved->value)){


                        $multipleAttendance = AppHelper::getAttendanceLimit();

                        if($multipleAttendance <= 1){
                            $shift = OfficeTime::where('id',$user->office_time_id)->first();
                            if(strtotime($leaveRequestDetail->end_time) == strtotime($shift['closing_time'])){
                                $updateData = [
                                    'check_out_at'=> $leaveRequestDetail->start_time,
                                    'check_out_type'=> EmployeeAttendanceTypeEnum::wifi->value
                                ];

                                $workedData = AttendanceHelper::calculateWorkedHour($leaveRequestDetail->start_time, $attendanceData->check_in_at,$attendanceData->user_id );

                                $updateData['worked_hour'] = $workedData['workedHours'];
                                $updateData['overtime'] = $workedData['overtime'];
                                $updateData['undertime'] = $workedData['undertime'];

                                $coordinate = $this->attendanceService->getCoordinates($leaveRequestDetail->requested_by);

                                $updateData['check_out_latitude'] =  $coordinate['latitude'];
                                $updateData['check_out_longitude'] = $coordinate['longitude'];


                                $attendanceStatus = $this->attendanceService->update($attendanceData, $updateData);

                                if($attendanceStatus){
                                    $this->userRepository->updateUserOnlineStatus($user,0);
                                }
                            }
                        }

                    }else{
                        $this->userRepository->updateUserOnlineStatus($user,0);

                    }

                }


            }
            DB::commit();
            return redirect()
                ->route('admin.leave-request.index')
                ->with('success', __('message.status_update'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    private function sendLeaveStatusNotification($notificationData,$userId)
    {
        SMPushHelper::sendLeaveStatusNotification($notificationData->title, $notificationData->description,$userId);
    }

    /**
     * @throws AuthorizationException
     */
    public function createLeaveRequest()
    {
        $this->authorize('create_time_leave_request');
        try {

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);

            return view($this->view . 'add', compact('companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function storeLeaveRequest(TimeLeaveStoreRequest $request)
    {
        $this->authorize('create_time_leave_request');
        try {
            $validatedData = $request->validated();

            $permissionKeyForNotification = 'employee_time_leave_request';

            $validatedData['referred_by'] = auth()->user()->id ?? null;
            $employee = $this->userRepository->findUserDetailById($validatedData['requested_by'], ['name']);

            DB::beginTransaction();
                $leaveRequest = $this->timeLeaveService->storeTimeLeaveRequest($validatedData);
            DB::commit();
            AppHelper::sendNotificationToAuthorizedUser(
                'Leave Request Notification',
                ucfirst(auth()->user()->name ?? 'admin'). ' on behalf of '.$employee->name . ' has requested leave from ' .
                $leaveRequest['start_time']. ' to ' .$leaveRequest['end_time'].
                ' on ' . AppHelper::convertLeaveDateFormat($leaveRequest['issue_date']) . ' Reason: ' . $validatedData['reasons'],
                $permissionKeyForNotification
            );
            return redirect()
                ->route('admin.leave-request.index')
                ->with('success', __('message.time_leve_submit'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function cancelRequestUpdate(Request $request, $leaveRequestId)
    {

        try {
            $validatedData = $request->validate([
                'status' => ['required', 'string',Rule::in([LeaveStatusEnum::approved->value,LeaveStatusEnum::rejected->value])],
            ]);
            DB::beginTransaction();
            $this->timeLeaveService->updateCancelReqeust($leaveRequestId, $validatedData);
            DB::commit();
            return redirect()
                ->route('admin.leave-request.index')
                ->with('success', __('message.leave_status_updated'));
        } catch (Exception $exception) {
            DB::rollBack();

            return redirect()->back()->with('danger', $exception->getMessage());
        }

    }

}
