<?php

namespace App\Services\Leave;

use App\Enum\LeaveStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Models\LeaveApproval;
use App\Models\OfficeTime;
use App\Repositories\LeaveRepository;
use App\Repositories\LeaveRequestApprovalRepository;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\TimeLeaveRepository;
use App\Repositories\UserRepository;
use App\Services\Attendance\AttendanceService;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use DateTime;
use Exception;
//use Illuminate\Support\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HigherOrderWhenProxy;
use function PHPUnit\Framework\isNull;

class LeaveService
{

    public function __construct(protected LeaveRepository $leaveRepo, protected LeaveTypeRepository $leaveTypeRepo,
                                protected LeaveRequestApprovalRepository $requestApprovalRepository, protected NotificationService $notificationService,
                                protected UserRepository $userRepository, protected AttendanceService $attendanceService,
    protected TimeLeaveRepository $timeLeaveRepository)
    {}

    /**
     * @param $filterParameters
     * @param $select
     * @param $with
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getAllEmployeeLeaveRequests($filterParameters, $select=['*'], $with=[])
    {

            if(AppHelper::ifDateInBsEnabled()){
                $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameters['year'],$filterParameters['month']);
                $filterParameters['start_date'] = $dateInAD['start_date'];
                $filterParameters['end_date'] = $dateInAD['end_date'];
            }

            return $this->leaveRepo->getAllEmployeeLeaveRequest($filterParameters,$select,$with);

    }

//    public function getAllEmployeeLeaveCancelRequests($filterParameters, $select=['*'], $with=[])
//    {
//
//        if(AppHelper::ifDateInBsEnabled()){
//            $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameters['year'],$filterParameters['month']);
//            $filterParameters['start_date'] = $dateInAD['start_date'];
//            $filterParameters['end_date'] = $dateInAD['end_date'];
//        }
//        return $this->leaveRepo->getAllEmployeeLeaveCancelRequest($filterParameters,$select,$with);
//
//    }

    /**
     * @param $filterParameters
     * @param $select
     * @param $with
     * @return array|Builder|Collection|HigherOrderWhenProxy
     * @throws Exception
     *
     */
    public function getAllLeaveRequestOfEmployee($filterParameters)
    {
        $dateInAD = AppHelper::leaveDatesForFilterData($filterParameters['month']);

        $filterParameters['start_date'] = $dateInAD['start_date'];
        $filterParameters['end_date'] = $dateInAD['end_date'];

        return $this->leaveRepo->getAllLeaveRequestDetailOfEmployee($filterParameters);

    }

    /**
     * @param $leaveRequestId
     * @param $select
     * @param $with
     * @return Builder|Model|object|null
     * @throws Exception
     */
    public function findEmployeeLeaveRequestById($leaveRequestId, $select=['*'], $with=[])
    {

        return $this->leaveRepo->findEmployeeLeaveRequestByEmployeeId($leaveRequestId,$select,$with);

    }

    public function findLeaveRequestReasonById($leaveRequestId)
    {

        return $this->leaveRepo->findEmployeeLeaveRequestReasonById($leaveRequestId);

    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function storeLeaveRequest($validatedData)
    {

        $leaveDate = $this->checkIfDateIsValidToRequestLeave($validatedData);

        $validatedData['no_of_days'] = $validatedData['leave_for'] == 'full_day' ? ($leaveDate['to']->diffInDays($leaveDate['from']) + 1) - $leaveDate['holidayCount'] : 0.5;
        $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
        $validatedData['leave_requested_date'] = Carbon::now()->format('Y-m-d h:i:s');

        if ($validatedData['leave_for'] == 'half_day') {
            $user = $this->userRepository->findUserDetailById($validatedData['requested_by'], ['id', 'office_time_id']);
            $shift = OfficeTime::where('id', $user->office_time_id)->first();
            $attendance = $this->attendanceService->findEmployeeTodayAttendanceDetail($validatedData['requested_by'], ['id', 'check_in_at', 'check_out_at']);

            if (empty($shift->halfday_mark_time)) {
                throw new Exception('Shift does not support half-day leaves.', 400);
            }

            if ($shift && $attendance) {

                $leaveFromCarbon = Carbon::parse($validatedData['leave_from']);
                $checkInTime = Carbon::parse('today ' . $attendance->check_in_at);

                $halfDayCarbon = Carbon::parse('today ' . $shift->halfday_mark_time);
                $closingCarbon = Carbon::parse('today ' . $shift->closing_time);

                if ($validatedData['leave_in'] == 'first_half') {

                    if ($checkInTime->lte($halfDayCarbon)) {
                        throw new Exception('You cannot apply leave for first half; you have already checked in.', 400);
                    }
                } else {

                    if (is_null($attendance->check_out_at) && $leaveFromCarbon->between($halfDayCarbon, $closingCarbon)) {
                        throw new Exception('You cannot apply leave for second half; your today\'s shift time is already on second half and you have not checked out yet.', 400);
                    }
                }
            } elseif ($shift && !$attendance) {
                $leaveFromCarbon = Carbon::parse($validatedData['leave_from']);
                $openingCarbon = Carbon::parse('today ' . $shift->opening_time);
                $halfDayCarbon = Carbon::parse('today ' . $shift->halfday_mark_time);
                $closingCarbon = Carbon::parse('today ' . $shift->closing_time);

                if ($validatedData['leave_in'] == 'first_half' && $leaveFromCarbon->between($openingCarbon,$halfDayCarbon)) {
                    throw new Exception('You cannot apply leave for first half; your today\'s shift time is already on first half.', 400);
                }
                if ($validatedData['leave_in'] == 'second_half' && $leaveFromCarbon->between($halfDayCarbon, $closingCarbon)) {
                    throw new Exception('You cannot apply leave for second half; your today\'s shift time is already on second half.', 400);
                }
            }
        }

        $this->checkEmployeeLeaveRequest($validatedData);


        Log::info('now save ');
        return $this->leaveRepo->store($validatedData);

    }

    /**
     * @param $validatedData
     * @return array
     * @throws Exception
     */
    private function checkIfDateIsValidToRequestLeave($validatedData)
    {
        $leave_from_date = substr($validatedData['leave_from'], 0, 10);
        $leave_to_date = isset($validatedData['leave_to']) ? substr($validatedData['leave_to'], 0, 10) : substr($validatedData['leave_from'], 0, 10);
        $holidayAndWeekendCount = 0;
        try {
            if (AppHelper::ifDateInBsEnabled()) {
                $leave_start = AppHelper::dateInYmdFormatEngToNep($leave_from_date);
                $leave_end = AppHelper::dateInYmdFormatEngToNep($leave_to_date);

                $from = AppHelper::getDayMonthYearFromDate($leave_start);
                $to = AppHelper::getDayMonthYearFromDate($leave_end);
                $leave_from = Carbon::createFromFormat('Y-m-d', $leave_from_date);
                $leave_to = Carbon::createFromFormat('Y-m-d', $leave_to_date);

                if ($from['year'] != $to['year']) {
                    throw new Exception(__('message.different_leave_bs_year'), 403);
                }
            } else {
                $leave_from = Carbon::createFromFormat('Y-m-d', $leave_from_date);
                $leave_to = Carbon::createFromFormat('Y-m-d', $leave_to_date);
                if ($leave_from->year != $leave_to->year) {
                    throw new Exception(__('message.different_leave_ad_year'), 403);
                }
            }

            $weekendAndHoliday = AppHelper::countWeekendAndHoliday();

            if(!$weekendAndHoliday){
                $holidayAndWeekendCount = AttendanceHelper::holidayAndWeekendCount($leave_from_date, $leave_to_date);
            }
            return [
                'from' => $leave_from,
                'to' => $leave_to,
                'holidayCount' => $holidayAndWeekendCount
            ];
        } catch (\Exception $e) {
            throw new Exception(__('message.invalid_date') . ': ' . $e->getMessage(), 400);
        }
    }


    /**
     * @param $validatedData
     * @return void
     * @throws Exception
     */
    private function checkEmployeeLeaveRequest($validatedData): void
    {

        $select= ['id','status'];
        $data['from_date'] = $validatedData['leave_from'];
        $data['requested_by'] = $validatedData['requested_by'] ?? getAuthUserCode();

        $employeeLatestPendingLeaveRequest = $this->leaveRepo->getEmployeeLatestLeaveRequestBetweenFromAndToDate($validatedData,$select);


        if($employeeLatestPendingLeaveRequest){
            throw new Exception(__('message.leave_status_error',['status'=>$employeeLatestPendingLeaveRequest->status]),400);
        }

        /** check if there is any time lave in between leave requested days */
        $check = $this->timeLeaveRepository->checkEmployeeTimeLeave($data['requested_by'],$validatedData);

        Log::info($check);
        if($check){
            throw new Exception(__('message.time_leave_pending_error'),400);
        }
        $leaveType =  $this->leaveTypeRepo->findLeaveTypeDetail($validatedData['leave_type_id'],  $data['requested_by']);
        Log::info($leaveType);
        $totalLeaveAllocated = $leaveType->leave_allocated;
        /**
         * unpaid leave are not allocated with any leave days .
         */
        if(is_null($leaveType->is_paid)){
            return;
        }

        $dates = AppHelper::getStartEndDate($data['from_date']) ;

        Log::info('year dates '.json_encode($dates));
        $totalLeaveTakenTillNow = $this->leaveRepo->employeeTotalApprovedLeavesForGivenLeaveType($validatedData['leave_type_id'], $dates);
        Log::info('leave taken '.$totalLeaveTakenTillNow);

        if( (int)$validatedData['no_of_days'] + (int)$totalLeaveTakenTillNow > $totalLeaveAllocated  ){
            throw new Exception(__('message.leave_exceed_error',['day'=>((int)$validatedData['no_of_days'] + (int)$totalLeaveTakenTillNow - $totalLeaveAllocated),'name'=>$leaveType->name]),400);
        }

    }


    /**
     * @param $validatedData
     * @param $leaveRequestId
     * @return Builder|Model|object
     * @throws Exception
     */
    public function updateLeaveRequestStatus($validatedData, $leaveRequestId)
    {

            $leaveRequestDetail = $this->findEmployeeLeaveRequestById($leaveRequestId);

            if(!$leaveRequestDetail){
                throw new Exception(__('message.leave_request_not_found'),404);
            }

            if(auth('admin')->user() ) {
                $this->leaveRepo->update($leaveRequestDetail,$validatedData);
                self::sendNotification($leaveRequestDetail,$validatedData['status']);
            }else{

                $approvalProcess = LeaveApproval::with(['approvalProcess'])->where('leave_type_id', $leaveRequestDetail->leave_type_id)->exists();

                if($approvalProcess){

                    $lastApprover = AppHelper::getLastApprover($leaveRequestDetail->leave_type_id, $leaveRequestDetail->requested_by);

                    $approvalData = [
                        'leave_request_id'=>$leaveRequestId,
                        'status'=>$validatedData['status'] == LeaveStatusEnum::approved->value ? 1 : 0,
                        'approved_by'=> auth()->user()->id,
                        'reason'=>$validatedData['admin_remark'],
                    ];

                    $permissionKey = 'access_admin_leave';
                    $roleArray = AppHelper::getRoleByPermission($permissionKey);

                    if($validatedData['status'] == LeaveStatusEnum::rejected->value){
                        if(($lastApprover == auth()->user()->id) || (in_array(auth()->user()->role_id,$roleArray) ) ){

                            $this->leaveRepo->update($leaveRequestDetail,$validatedData);
                            if(!in_array(auth()->user()->role_id,$roleArray)){
                                $this->saveLeaveRequestApproval($approvalData);
                            }
                        }else{
                            $this->saveLeaveRequestApproval($approvalData);
                        }
                    }else{
                        if (($lastApprover == auth()->user()->id)) {

                            self::sendNotification($leaveRequestDetail,$validatedData['status']);
                            $this->leaveRepo->update($leaveRequestDetail,$validatedData);
                        }else{
                            $approver = AppHelper::getNextApprover($leaveRequestId, $leaveRequestDetail->leave_type_id, $leaveRequestDetail->requested_by);

                            $employee = $this->userRepository->findUserDetailById($leaveRequestDetail->requested_by, ['id','name']);
                            $title = __('message.leave_notification_title');
                            $description = ucfirst(auth()->user()->name) .' has '. ucfirst($validatedData['status']) . ' leave requested by '. ucfirst($employee->name).'. reason: '. $approvalData['reason'];

                            SMPushHelper::sendLeaveNotification($title, $description,$approver);
                        }
                        $this->saveLeaveRequestApproval($approvalData);
                    }

                }else{

                    $this->leaveRepo->update($leaveRequestDetail,$validatedData);
                    self::sendNotification($leaveRequestDetail,$validatedData['status']);
                }

            }

        return $leaveRequestDetail;

    }


    /**
     * @throws Exception
     */
    public function updateCancelReqeust($leaveRequestId,$validatedData)
    {

            $leaveRequestDetail = $this->findEmployeeLeaveRequestById($leaveRequestId);

            if(!$leaveRequestDetail){
                throw new Exception(__('message.leave_request_not_found'),404);
            }

            if($validatedData['status'] == LeaveStatusEnum::approved->value){
                $updateData = [
                    'status' => LeaveStatusEnum::cancelled->value,
                    'cancel_request' => 0,
                    'cancellation_approved_at' => Carbon::now(),
                    'cancellation_approved_by' => auth()->user()->id ?? null,
                ];
            }else{
                $updateData = ['cancel_request' => 0];
            }

            $this->leaveRepo->update($leaveRequestDetail, $updateData);

            $title = __('message.leave_notification_title');
            $description = 'Your leave cancel request for leave on '. AppHelper::formatDateForView($leaveRequestDetail->leave_from) .' has been '.ucfirst($validatedData['status']);

            SMPushHelper::sendLeaveNotification($title, $description,$leaveRequestDetail->requested_by);

        return $leaveRequestDetail;


    }

    /**
     * @return array|void
     * @throws Exception
     */
    public function getLeaveCountDetailOfEmployeeOfTwoMonth()
    {
            $allLeaveRequest = $this->leaveRepo->getLeaveCountDetailOfEmployeeOfTwoMonth();
            if($allLeaveRequest){
                $leaveDates = [];
                foreach($allLeaveRequest as $key => $value){
                    $leaveRequestedDays = $value->no_of_days;
                    $i=0;
                    $fromDate = Carbon::parse( $value->leave_from)->format('Y-m-d');
                    for($i; $i<$leaveRequestedDays; $i++){
                        $leaveDates[] = date('Y-m-d', strtotime("+$i day", strtotime($fromDate)));
                    }
                }
                $leaveDetail = array_count_values($leaveDates);
                $dateWithNumberOfEmployeeOnLeave = [];
                foreach($leaveDetail as $key => $value){
                    $data = [];
                    $data['date']= $key;
                    $data['leave_count']= $value;
                    $dateWithNumberOfEmployeeOnLeave[] = $data;
                }
                return $dateWithNumberOfEmployeeOnLeave;
            }

    }

    /**
     * @param $filterParameter
     * @return mixed
     * @throws Exception

     */
    public function getAllEmployeeLeaveDetailBySpecificDay($filterParameter)
    {

        return $this->leaveRepo->getAllEmployeeLeaveDetailBySpecificDay($filterParameter);

    }
    public function getAllEmployeeLeaveDetail($filterParameter)
    {

        return $this->leaveRepo->getAllEmployeeLeaveDetail($filterParameter);

    }

    /**
     * @param $leaveRequestId
     * @param $employeeId
     * @param $select
     * @return Builder|Model|object
     * @throws Exception
     */
    public function findLeaveRequestDetailByIdAndEmployeeId($leaveRequestId, $employeeId, $select=['*'])
    {

        $leaveRequestDetail = $this->leaveRepo->findEmployeeLeaveRequestDetailById($leaveRequestId,$employeeId,$select);
        if(!$leaveRequestDetail){
            throw new Exception(__('message.leave_request_not_found'),404);
        }
        return $leaveRequestDetail;

    }

    /**
     * @param $validatedData
     * @param $leaveRequestDetail
     * @throws Exception
     * @return mixed
     */
    public function cancelLeaveRequest($validatedData, $leaveRequestDetail)
    {

        DB::beginTransaction();
            $this->leaveRepo->update($leaveRequestDetail,$validatedData);
        DB::commit();
        return $leaveRequestDetail;

    }



    /**
     * @param $validatedData
     * @return void
     * @throws Exception
     */
    private function checkExistingLeaveRequest($validatedData): void
    {


            $date = date('Y-m-d', strtotime($validatedData['issue_date']));

            $employeeLatestPendingLeaveRequest = $this->leaveRepo->getEmployeeLatestLeaveRequestDate($date);
            if($employeeLatestPendingLeaveRequest){
                throw new Exception(__('message.leave_pending_error',['status'=>$employeeLatestPendingLeaveRequest->status]),400);
            }


    }

    private function saveLeaveRequestApproval($data): void
    {
        $this->requestApprovalRepository->create($data);
    }


    private function sendLeaveStatusNotification($notificationData,$userId)
    {
        SMPushHelper::sendLeaveStatusNotification($notificationData->title, $notificationData->description,$userId);
    }

    private function sendNotification ($leaveRequestDetail, $status): void
    {
        $notificationData = [
            'title' => 'Leave Request Notification',
            'type' => 'leave',
            'user_id' => [$leaveRequestDetail->requested_by],
            'description' => 'Your ' . $leaveRequestDetail->no_of_days . ' day leave request requested on ' . date('M d Y h:i A', strtotime($leaveRequestDetail->leave_requested_date)) . ' has been ' . ucfirst($status),
            'notification_for_id' => $leaveRequestDetail->id,
        ];

        $notification = $this->notificationService->store($notificationData);

        if($notification){
            $this->sendLeaveStatusNotification($notification,$leaveRequestDetail->requested_by);
        }
    }
    public function getApprovalHistory($leaveRequestDetail): \Illuminate\Support\Collection
    {
        return $this->leaveRepo->getApprovalHistory($leaveRequestDetail);
    }



}
