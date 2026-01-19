<?php

namespace App\Services\Attendance;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\DateConverter;
use App\Models\Attendance;
use App\Models\User;
use App\Repositories\AppSettingRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\BranchRepository;
use App\Repositories\LeaveRepository;
use App\Repositories\RouterRepository;
use App\Repositories\TimeLeaveRepository;
use App\Repositories\UserRepository;
use App\Services\Holiday\HolidayService;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceService
{

    public function __construct(protected AttendanceRepository $attendanceRepo,
                                protected UserRepository       $userRepo,
                                protected RouterRepository     $routerRepo,
                                protected AppSettingRepository $appSettingRepo,
                                protected LeaveRepository      $leaveRepo,
                                protected TimeLeaveRepository  $timeLeaveRepository,
                                protected BranchRepository     $branchRepository,
                                protected HolidayService     $holidayService,
    )
    {
    }

    /**
     * @param $filterParameter
     * @return mixed
     * @throws Exception
     */
    public function getAllCompanyEmployeeAttendanceDetailOfTheDay($filterParameter): mixed
    {

        if ($filterParameter['date_in_bs']) {
            $filterParameter['attendance_date'] = AppHelper::dateInYmdFormatNepToEng($filterParameter['attendance_date']);
        }

        return $this->attendanceRepo->getAllCompanyEmployeeAttendanceDetailOfTheDay($filterParameter);

    }

    /**
     * @param $filterParameter
     * @param array $select
     * @param array $with
     * @return Builder[]|Collection
     * @throws Exception
     */
    public function getEmployeeAttendanceDetailOfTheMonth($filterParameter, array $select = ['*'], array $with = []): Collection|array
    {
        try {
//            $days = $filterParameter['date_in_bs']
//                ? AppHelper::getTotalDaysInNepaliMonth($filterParameter['year'], $filterParameter['month'])
//                : AttendanceHelper::getTotalNumberOfDaysInSpecificMonth($filterParameter['month'], $filterParameter['year']);

            if ($filterParameter['date_in_bs']) {
                $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameter['year'], $filterParameter['month']);
                $filterParameter['start_date'] = date('Y-m-d', strtotime($dateInAD['start_date'])) ?? null;

                $filterParameter['end_date'] = date('Y-m-d', strtotime($dateInAD['end_date'])) ?? null;
            } else {
                $firstDay = $filterParameter['year'] . '-' . $filterParameter['month'] . '-' . '01';
                $filterParameter['start_date'] = date('Y-m-d', strtotime($firstDay));
                $filterParameter['end_date'] = date('Y-m-t', strtotime($firstDay));
            }

            $today = date('Y-m-d');
            if ($filterParameter['end_date'] > $today) {
                $filterParameter['end_date'] = $today;
            }


            $employeeMonthlyAttendance = [];

            $with = ['officeTime:id,shift_type,opening_time,closing_time'];
            $attendanceDetail = $this->attendanceRepo->getEmployeeAttendanceDetailOfTheMonth($filterParameter, $select, $with);

            if (($filterParameter['start_date'] <= $today) && $attendanceDetail->isNotEmpty()) {
                do {
                    $employeeMonthlyAttendance[] = [
                        'attendance_date' => $filterParameter['start_date'],
                    ];

                    $filterParameter['start_date'] = date('Y-m-d', strtotime("+1 day", strtotime($filterParameter['start_date'])));

                } while ($filterParameter['start_date'] <= $filterParameter['end_date']);
            }
            foreach ($attendanceDetail as $key => $value) {
                if ($filterParameter['date_in_bs']) {
                    $getDay = AppHelper::getNepaliDay($value->attendance_date);
                } else {
                    $getDay = date('d', strtotime($value->attendance_date));
                }
                $extraData = AttendanceHelper::getOverAndUnderTimeData($value);

                $attendanceData = [
                    'id' => $value->id,
                    'user_id' => $value->user_id,
                    'attendance_date' => $value->attendance_date,
                    'check_in_at' => $value->check_in_at,
                    'check_out_at' => $value->check_out_at,
                    'check_in_latitude' => $value->check_in_latitude,
                    'check_out_latitude' => $value->check_out_latitude,
                    'check_in_longitude' => $value->check_in_longitude,
                    'check_out_longitude' => $value->check_out_longitude,
                    'attendance_status' => $value->attendance_status,
                    'note' => $value->note,
                    'edit_remark' => $value->edit_remark,
                    'created_by' => $value->created_by,
                    'created_at' => $value->created_at,
                    'updated_at' => $value->updated_at,
                    'check_in_type' => $value->check_in_type,
                    'check_out_type' => $value->check_out_type,
                    'worked_hour' => $value->worked_hour,
                    'working_hour' => $extraData['workingHourMin'],
                    'night_checkin' => $value->night_checkin,
                    'night_checkout' => $value->night_checkout,
                    'shift' => $value->officeTime->shift_type ?? '',
                    'overtime' => $extraData['overTime'] ?? 0,
                    'undertime' => isset($value->check_out_at) ? $extraData['underTime'] : 0,
                ];

                if (!isset($employeeMonthlyAttendance[$getDay - 1])) {
                    $employeeMonthlyAttendance[$getDay - 1] = [];
                }

                $employeeMonthlyAttendance[$getDay - 1]['data'][] = $attendanceData;
            }
            return $employeeMonthlyAttendance;
        } catch (Exception $e) {
            throw $e;
        }
    }


    /**
     * @throws Exception
     */
    public function getEmployeeAttendanceDetailOfTheMonthFromUserRepo($filterParameter, $select = ['*'], $with = [])
    {
        if (AppHelper::ifDateInBsEnabled()) {
            $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
            $filterParameter['year'] = $nepaliDate['year'];
            $filterParameter['month'] = $filterParameter['month'] ?? $nepaliDate['month'];
            $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameter['year'], $filterParameter['month']);
            $filterParameter['start_date'] =  date('Y-m-d', strtotime($dateInAD['start_date']));
            $filterParameter['end_date'] = date('Y-m-d', strtotime($dateInAD['end_date']));

        } else {
            $filterParameter['year'] = AppHelper::getCurrentYear();
            $filterParameter['month'] = $filterParameter['month'] ?? date('m');
            $firstDay = $filterParameter['year'] . '-' . $filterParameter['month'] . '-' . '01';
            $filterParameter['start_date'] = date('Y-m-d', strtotime($firstDay));
            $filterParameter['end_date'] = date('Y-m-t', strtotime($firstDay));
        }

        $today = date('Y-m-d');

        if (strtotime($filterParameter['end_date']) > strtotime($today)) {
            $filterParameter['end_date'] = $today;
        }


        $employeeMonthlyAttendance = [];

        $userAttendanceDetail = $this->userRepo->getApiEmployeeAttendanceDetailOfTheMonth($filterParameter, $select, $with);

        $userDetail = [
            'user_id'=>$userAttendanceDetail->id,
            'name'=>$userAttendanceDetail->name,
            'email'=>$userAttendanceDetail->email,
        ];
        $todayAttendance = $userAttendanceDetail->employeeTodayAttendance;
        $leaveArray = $this->leaveRepo->getMonthLeaveRequestList($filterParameter);
        $holidayArray = $this->holidayService->getActiveHolidayList($filterParameter);
        $weekendArray = AppHelper::getMonthWeekendDates($filterParameter);
        if ((strtotime($filterParameter['start_date']) <= strtotime($filterParameter['end_date']))) {
            do {

                $startDate = Carbon::createFromFormat('Y-m-d', $filterParameter['start_date'])->format('Y-m-d');
                $attendanceData = $userAttendanceDetail->employeeAttendance->where('attendance_date', $startDate);

                if($attendanceData->isNotEmpty()){
                    foreach($attendanceData as $attendance){

                        $extraData = AttendanceHelper::getOverAndUnderTimeData($attendance);
                        $employeeMonthlyAttendance[] = [
                            'id' => $attendance->id,
                            'attendance_date' => AppHelper::dateInDDMMFormat($filterParameter['start_date'], false),
                            'attendance_date_nepali' => AppHelper::dateInDDMMFormat($attendance->attendance_date),
                            'week_day' => AttendanceHelper::getWeekDayInShortForm($attendance->attendance_date),
                            'check_in' => isset($attendance->check_in_at) ? AttendanceHelper::changeTimeFormatForAttendanceView($attendance->check_in_at) : (isset($attendance->night_checkin) ? AttendanceHelper::changeTimeFormatForAttendanceView($attendance->night_checkin) : '-'),
                            'check_out' => isset($attendance->check_out_at) ? AttendanceHelper::changeTimeFormatForAttendanceView($attendance->check_out_at) : (isset($attendance->night_checkout) ? AttendanceHelper::changeTimeFormatForAttendanceView($attendance->night_checkout) : '-'),
                            'worked_hours_min' => (double)$attendance->worked_hour  ?? 0,
                            'worked_hours' => $attendance->worked_hour  ? floor($attendance->worked_hour / 60) . 'h ' . round(($attendance->worked_hour - floor($attendance->worked_hour / 60) * 60)) . 'm': '0h 0m',
                            'working_hours_min' => (double)$extraData['workingHourMin']  ?? 0,
                            'working_hours' => $extraData['workingHourMin']  ? floor($extraData['workingHourMin'] / 60) . 'h ' . round(($extraData['workingHourMin'] - floor($extraData['workingHourMin'] / 60) * 60)) . 'm': '0h 0m',
                            'overtime' => isset($extraData['overTime']) ? (floor($extraData['overTime'] / 60) . 'h ' . round(($extraData['overTime'] - floor($extraData['overTime'] / 60) * 60)) . 'm') : '',
                            'is_overtime' => $extraData['isOverTime'],
                            'undertime' => isset($attendance->check_out_at) ? (floor($extraData['underTime'] / 60) . 'h ' . round(($extraData['underTime'] - floor($extraData['underTime'] / 60) * 60)) . 'm') : '',
                            'is_undertime' => $extraData['isUnderTime'],
                            'status' => 'Present',
                        ];
                    }
                }else{
                    if(in_array($filterParameter['start_date'],$leaveArray)){
                        $employeeMonthlyAttendance[] = [
                            'id' => 0,
                            'attendance_date' => AppHelper::dateInDDMMFormat($filterParameter['start_date'], false),
                            'attendance_date_nepali' => AppHelper::dateInDDMMFormat($filterParameter['start_date']),
                            'week_day' => AttendanceHelper::getWeekDayInShortForm($filterParameter['start_date']),
                            'check_in' =>  '-',
                            'check_out' =>  '-',
                            'worked_hours_min' =>  0,
                            'worked_hours' => '0h 0m',
                            'working_hours_min' => 0,
                            'working_hours' => '0h 0m',
                            'overtime' => '',
                            'is_overtime' => false,
                            'undertime' =>'',
                            'is_undertime' => false,
                            'status' => 'Leave',
                        ];
                    }elseif(in_array($filterParameter['start_date'],$holidayArray)){
                        $employeeMonthlyAttendance[] = [
                            'id' => 0,
                            'attendance_date' => AppHelper::dateInDDMMFormat($filterParameter['start_date'], false),
                            'attendance_date_nepali' => AppHelper::dateInDDMMFormat($filterParameter['start_date']),
                            'week_day' => AttendanceHelper::getWeekDayInShortForm($filterParameter['start_date']),
                            'check_in' =>  '-',
                            'check_out' =>  '-',
                            'worked_hours_min' =>  0,
                            'worked_hours' => '0h 0m',
                            'working_hours_min' => 0,
                            'working_hours' => '0h 0m',
                            'overtime' => '',
                            'is_overtime' => false,
                            'undertime' =>'',
                            'is_undertime' => false,
                            'status' => 'Holiday',
                        ];
                    }elseif(in_array($filterParameter['start_date'],$weekendArray)){
                        $employeeMonthlyAttendance[] = [
                            'id' => 0,
                            'attendance_date' => AppHelper::dateInDDMMFormat($filterParameter['start_date'], false),
                            'attendance_date_nepali' => AppHelper::dateInDDMMFormat($filterParameter['start_date']),
                            'week_day' => AttendanceHelper::getWeekDayInShortForm($filterParameter['start_date']),
                            'check_in' =>  '-',
                            'check_out' =>  '-',
                            'worked_hours_min' =>  0,
                            'worked_hours' => '0h 0m',
                            'working_hours_min' => 0,
                            'working_hours' => '0h 0m',
                            'overtime' => '',
                            'is_overtime' => false,
                            'undertime' =>'',
                            'is_undertime' => false,
                            'status' => 'Off Day',
                        ];
                    }else{
                        $employeeMonthlyAttendance[] = [
                            'id' => 0,
                            'attendance_date' => AppHelper::dateInDDMMFormat($filterParameter['start_date'], false),
                            'attendance_date_nepali' => AppHelper::dateInDDMMFormat($filterParameter['start_date']),
                            'week_day' => AttendanceHelper::getWeekDayInShortForm($filterParameter['start_date']),
                            'check_in' =>  '-',
                            'check_out' =>  '-',
                            'worked_hours_min' =>  0,
                            'worked_hours' => '0h 0m',
                            'working_hours_min' => 0,
                            'working_hours' => '0h 0m',
                            'overtime' => '',
                            'is_overtime' => false,
                            'undertime' =>'',
                            'is_undertime' => false,
                            'status' => 'Absent',
                        ];
                    }
                }

                $filterParameter['start_date'] = date('Y-m-d', strtotime("+1 day", strtotime($filterParameter['start_date'])));

            } while (strtotime($filterParameter['start_date']) <= strtotime($filterParameter['end_date']));
        }

        return
            [
                'userDetail' =>$userDetail,
                'todayAttendance' =>$todayAttendance,
                'employeeMonthlyAttendance' =>$employeeMonthlyAttendance,
            ];

    }

    public function findEmployeeTodayAttendanceDetail($userId, $select = ['*'])
    {
        return $this->attendanceRepo->findEmployeeTodayCheckInDetail($userId, $select);
    }

    public function findEmployeeAttendanceDetailForNightShift($userId, $select = ['*'])
    {
        return $this->attendanceRepo->findEmployeeCheckInDetailForNightShift($userId, $select);
    }

    public function findEmployeeTodayAttendanceNumbers($userId)
    {
        return $this->attendanceRepo->todayAttendanceDetail($userId);
    }


    /**
     * @throws Exception
     */
    public function newCheckIn($validatedData)
    {
        $shift = AppHelper::getUserShift($validatedData['user_id']);

        if($validatedData['allow_holiday_check_in'] == 0){
            $employeeLeaveDetail = $this->leaveRepo->findEmployeeApprovedLeaveForCurrentDate($validatedData, ['id','no_of_days','leave_for','leave_in']);
            if ($employeeLeaveDetail) {

                if($employeeLeaveDetail->leave_for == 'half_day')
                {
                    $checkInCarbon = Carbon::now();
                    $openingCarbon = Carbon::parse('today ' . $shift->opening_time);
                    $halfDayCarbon = Carbon::parse('today ' . $shift->halfday_mark_time);
                    $closingCarbon = Carbon::parse('today ' . $shift->closing_time);

                    if($employeeLeaveDetail->leave_in == 'first_half'){
                        if($checkInCarbon->between($openingCarbon, $halfDayCarbon)) {
                            throw new Exception(__('message.first_half_checkin_error',['opening'=>$shift->opening_time,'half'=>$shift->halfday_mark_time]), 400);
                        }
                    }else{
                        if($checkInCarbon->between($halfDayCarbon, $closingCarbon)) {
                            throw new Exception(__('message.second_half_checkin_error',['half'=>$shift->halfday_mark_time,'closing'=>$shift->closing_time]), 400);
                        }
                    }
                }else{
                    throw new Exception(__('message.leave_attendance'), 400);
                }
            }

            $checkHolidayAndWeekend = AttendanceHelper::isHolidayOrWeekendOnCurrentDate();

            if ($checkHolidayAndWeekend) {
                throw new Exception(__('message.holiday_attendance'), 403);
            }

        }

        $checkInAt = Carbon::now()->toTimeString();

        if ($shift && isset($shift->opening_time)) {
            $checkInAt = Carbon::createFromTimeString(now()->toTimeString());
            $openingTime = Carbon::createFromFormat('H:i:s', $shift->opening_time);
            $timeLeave = $this->timeLeaveRepository->getEmployeeApprovedTimeLeave(date('Y-m-d'), $validatedData['user_id']);

            if (!($timeLeave) && ($shift->is_early_check_in == 1 && $shift->checkin_before)) {
                $checkInTimeAllowed = $openingTime->copy()->subMinutes($shift->checkin_before);

                if ($checkInAt->lt($checkInTimeAllowed)) {
                    throw new Exception(__('message.earlier_checkin'), 400);
                }
            }


            if (!($timeLeave) && ($shift->is_late_check_in == 1 && $shift->checkin_after)) {

                $checkInTimeAllowed = $openingTime->copy()->addMinutes($shift->checkin_after);

                if ($checkInAt->greaterThan($checkInTimeAllowed)) {

                    throw new Exception(__('message.late_checkin'), 400);
                }
            }

            if (isset($timeLeave) && ((strtotime($timeLeave->end_time) > strtotime($checkInAt)) && (strtotime($timeLeave->start_time) < strtotime($checkInAt)))) {
                $checkInAt = Carbon::parse($timeLeave->end_time)->toTimeString();
            }

        }


        $validatedData['attendance_date'] = Carbon::now()->format('Y-m-d');

        if ($validatedData['night_shift']) {
            $validatedData['night_checkin'] = $checkInAt;
        } else {
            $validatedData['check_in_at'] = $checkInAt;
        }

        $coordinate = $this->getCoordinates($validatedData['user_id']);

        $validatedData['check_in_latitude'] = $validatedData['check_in_latitude'] ?? $coordinate['latitude'];
        $validatedData['check_in_longitude'] = $validatedData['check_in_longitude'] ?? $coordinate['longitude'];

        $locationData = [
            'latitude'=> $validatedData['check_in_latitude'],
            'longitude'=>$validatedData['check_in_longitude'],
            'employee_id'=>$validatedData['user_id'],

        ];
        $this->userRepo->setEmployeeLocation($locationData);
        $attendance = $this->attendanceRepo->storeAttendanceDetail($validatedData);
        if ($attendance) {

            $this->updateUserOnlineStatus($attendance->user_id, User::ONLINE);
        }
        return $attendance;

    }


    /**
     * @throws Exception
     */
    public function newCheckOut($attendanceData, $validatedData)
    {
        $checkOut = Carbon::now()->toTimeString();
        $timeLeaveInMinutes = 0;

        if (isset($attendanceData->check_in_at)) {

            $checkInWithBuffer = Carbon::parse($attendanceData->check_in_at)->addMinutes()->toTimeString();

            if ($checkOut < $checkInWithBuffer) {
                throw new Exception(__('message.just_check_in'), 400);
            }
        }

        $shift = AppHelper::getUserShift($validatedData['user_id']);


        $employeeLeaveDetail = $this->leaveRepo->findEmployeeApprovedLeaveForCurrentDate($validatedData, ['id','no_of_days','leave_for','leave_in']);
        if ($employeeLeaveDetail) {

            if($employeeLeaveDetail->leave_for == 'half_day')
            {
                $checkOutCarbon = Carbon::now();
                $openingCarbon = Carbon::parse('today ' . $shift->opening_time);
                $halfDayCarbon = Carbon::parse('today ' . $shift->halfday_mark_time);
                $closingCarbon = Carbon::parse('today ' . $shift->closing_time);

                if($employeeLeaveDetail->leave_in == 'first_half'){
                    if($checkOutCarbon->between($openingCarbon, $halfDayCarbon)) {
                        throw new Exception(__('message.first_half_checkout_error',['opening'=>$shift->opening_time,'half'=>$shift->halfday_mark_time]), 400);
                    }
                }else{
                    if($checkOutCarbon->between($halfDayCarbon, $closingCarbon)) {
                        $checkOut = Carbon::parse($shift->halfday_mark_time)->toTimeString();
                    }
                }
            }
        }




        if ($shift && isset($shift->closing_time)) {
            $openingTime = Carbon::createFromFormat('H:i:s', $shift->closing_time);
            $checkOutAt = Carbon::createFromTimeString(now()->toTimeString());

            $timeLeave = $this->timeLeaveRepository->getEmployeeApprovedTimeLeave(date('Y-m-d'), $validatedData['user_id']);

            if (!isset($timeLeave) && ($shift->is_early_check_out == 1 && $shift->checkout_before)) {

                $checkOutTimeAllowed = $openingTime->copy()->subMinutes($shift->checkout_before);

                if ($checkOutAt->lt($checkOutTimeAllowed)) {
                    throw new Exception(__('message.early_checkout'), 400);
                }
            }

            if (!isset($timeLeave) && ($shift->is_late_check_out == 1 && $shift->checkout_after)) {

                $checkOutTimeAllowed = $openingTime->copy()->addMinutes($shift->checkout_after);

                if ($checkOutAt->greaterThan($checkOutTimeAllowed)) {
                    throw new Exception(__('message.late_checkout'), 400);
                }
            }

            if (isset($timeLeave) && (strtotime($timeLeave->end_time) == strtotime($shift->closing_time))) {
                $checkOut = Carbon::parse($timeLeave->start_time)->toTimeString();
            }

            if (isset($timeLeave) && (strtotime($timeLeave->start_time) < strtotime($checkOut) && strtotime($timeLeave->end_time) > strtotime($checkOut))) {
                $checkOut = Carbon::parse($timeLeave->start_time)->toTimeString();
            }


            if (isset($timeLeave) && (strtotime($timeLeave->end_time) < strtotime($checkOut) && strtotime($timeLeave->start_time) >= strtotime($attendanceData->check_in_at))) {
                $timeLeaveInMinutes = Carbon::parse($timeLeave->end_time)->diffInMinutes(Carbon::parse($timeLeave->start_time));
            }

        }

        if ($validatedData['night_shift']) {
            $validatedData['night_checkout'] = Carbon::now()->toDateString() . ' ' . $checkOut;
            $workedData = AttendanceHelper::calculateWorkedHour($validatedData['night_checkout'], $attendanceData->night_checkin, $attendanceData->user_id);
        } else {
            $validatedData['check_out_at'] = $checkOut;
            $workedData = AttendanceHelper::calculateWorkedHour($checkOut, $attendanceData->check_in_at, $attendanceData->user_id);

        }


        $validatedData['worked_hour'] = $workedData['workedHours'] - $timeLeaveInMinutes;
        $validatedData['overtime'] = $workedData['overtime'];
        $validatedData['undertime'] = $workedData['undertime'];

        $coordinate = $this->getCoordinates($validatedData['user_id']);

        $validatedData['check_out_latitude'] = $validatedData['check_out_latitude'] ?? $coordinate['latitude'];
        $validatedData['check_out_longitude'] = $validatedData['check_out_longitude'] ?? $coordinate['longitude'];

        $locationData = [
            'latitude'=> $validatedData['check_out_latitude'],
            'longitude'=>$validatedData['check_out_longitude'],
            'employee_id'=>$validatedData['user_id'],

        ];
        $this->userRepo->setEmployeeLocation($locationData);
        $attendanceCheckOut = $this->attendanceRepo->updateAttendanceDetail($attendanceData, $validatedData);


        $this->updateUserOnlineStatus($validatedData['user_id'], User::OFFLINE);

        return $attendanceCheckOut;

    }

    /**
     * @Deprecated Don't use this now
     */
    public function employeeCheckIn($validatedData)
    {
        try {
            $select = ['id', 'check_out_at'];
            $userTodayCheckInDetail = $this->attendanceRepo->findEmployeeTodayCheckInDetail($validatedData['user_id'], $select);
            if ($userTodayCheckInDetail) {
                throw new Exception('Sorry ! employee cannot check in twice a day.', 400);
            }

            $employeeLeaveDetail = $this->leaveRepo->findEmployeeApprovedLeaveForCurrentDate($validatedData, ['id']);
            if ($employeeLeaveDetail) {
                throw new Exception('Cannot check in when leave request is Approved/Pending.', 400);
            }

            $checkHolidayAndWeekend = AttendanceHelper::isHolidayOrWeekendOnCurrentDate();
            if (!$checkHolidayAndWeekend) {
                throw new Exception('Check In not allowed on holidays or on office Off Days', 403);
            }

            $validatedData['attendance_date'] = Carbon::now()->format('Y-m-d');
            $validatedData['check_in_at'] = Carbon::now()->toTimeString();

            DB::beginTransaction();
            $attendance = $this->attendanceRepo->storeAttendanceDetail($validatedData);
            if ($attendance) {
                $this->updateUserOnlineStatus($attendance->user_id, User::ONLINE);
            }
            DB::commit();
            return $attendance;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @Deprecated Don't use this now
     */
    public function employeeCheckOut($validatedData)
    {
        try {

            $this->authorizeAttendance($validatedData['router_bssid'], $validatedData['user_id']);

            $select = ['id', 'check_out_at', 'check_in_at', 'user_id'];
            $userTodayCheckInDetail = $this->attendanceRepo->findEmployeeTodayCheckInDetail($validatedData['user_id'], $select);
            if (!$userTodayCheckInDetail) {
                throw new Exception('Not checked in yet', 400);
            }
            if ($userTodayCheckInDetail->check_out_at) {
                throw new Exception('Employee already checked out for today', 400);
            }

            $checkOut = Carbon::now()->toTimeString();

            $workedData = AttendanceHelper::calculateWorkedHour($checkOut, $userTodayCheckInDetail->check_in_at, $userTodayCheckInDetail->user_id);

            $validatedData['check_out_at'] = $checkOut;
            $validatedData['worked_hour'] = $workedData['workedHours'];
            $validatedData['overtime'] = $workedData['overtime'];
            $validatedData['undertime'] = $workedData['undertime'];


            DB::beginTransaction();
            $attendanceCheckOut = $this->attendanceRepo->updateAttendanceDetail($userTodayCheckInDetail, $validatedData);
            $this->updateUserOnlineStatus($validatedData['user_id'], User::OFFLINE);
            DB::commit();
            return $attendanceCheckOut;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function updateUserOnlineStatus($userId, $loginStatus)
    {

        $userDetail = $this->findUserDetailById($userId);
        if ($userDetail->online_status == $loginStatus) {
            return;
        }

        $this->userRepo->updateUserOnlineStatus($userDetail, $loginStatus);


    }

    /**
     * @throws Exception
     */
    public function updateUserOnlineStatusToOffline($userId)
    {

        $userDetail = $this->findUserDetailById($userId);

        $this->userRepo->updateUserOnlineStatus($userDetail, User::OFFLINE);

    }

    /**
     * @throws Exception
     */
    public function findUserDetailById($userId, $select = ['*'])
    {

        $employeeDetail = $this->userRepo->findUserDetailById($userId, $select);
        if (!$employeeDetail) {
            throw new Exception(__('message.user_not_found'), 403);
        }
        return $employeeDetail;

    }
    /**
     * @throws Exception
     */
    public function newAuthorizeAttendance($routerBSSID, $userId)
    {

            $slug = 'override-bssid';
            $overrideBSSID = $this->appSettingRepo->findAppSettingDetailBySlug($slug);
            if ($overrideBSSID && $overrideBSSID->status == 1) {
                $select = ['workspace_type'];
                $employeeWorkSpace = $this->findUserDetailById($userId, $select);
                if ($employeeWorkSpace->workspace_type == User::OFFICE) {
                    $checkEmployeeRouter = $this->routerRepo->findRouterDetailBSSID($routerBSSID);
                    if (!$checkEmployeeRouter) {
                        throw new Exception(__('message.attendance_outside'));
                    }
                    $branch = $this->branchRepository->findBranchDetailById($checkEmployeeRouter->branch_id);

                    return ['latitude' => $branch->branch_location_latitude, 'longitude' => $branch->branch_location_longitude];
                }

            }

    }

    /**
     * @Deprecated Don't use this now
     * @throws Exception
     */
    public function authorizeAttendance($routerBSSID, $userId): void
    {
        $slug = 'override-bssid';
        $overrideBSSID = $this->appSettingRepo->findAppSettingDetailBySlug($slug);
        if ($overrideBSSID && $overrideBSSID->status == 1) {
            $select = ['workspace_type'];
            $employeeWorkSpace = $this->findUserDetailById($userId, $select);
            if ($employeeWorkSpace->workspace_type == User::OFFICE) {
                $checkEmployeeRouter = $this->routerRepo->findRouterDetailBSSID($routerBSSID);
                if (!$checkEmployeeRouter) {
                    throw new Exception(__('message.attendance_outside'));
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function changeAttendanceStatus($id)
    {

        $attendanceDetail = $this->attendanceRepo->findAttendanceDetailById($id);
        if (!$attendanceDetail) {
            throw new Exception(__('message.attendance_not_found'), 403);
        }

        $this->attendanceRepo->updateAttendanceStatus($attendanceDetail);


    }

    /**
     * @throws Exception
     */
    public function findAttendanceDetailById($id, $select = ['*'])
    {
        $attendanceDetail = $this->attendanceRepo->findAttendanceDetailById($id);
        if (!$attendanceDetail) {
            throw new Exception(__('message.attendance_not_found'), 404);
        }
        return $attendanceDetail;
    }

    public function update($attendanceDetail, $validatedData)
    {

        return $this->attendanceRepo->updateAttendanceDetail($attendanceDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function delete($id)
    {

            $attendanceDetail = $this->findAttendanceDetailById($id);

            $this->attendanceRepo->delete($attendanceDetail);

    }

    public function addAttendance($validatedData)
    {


            return $this->attendanceRepo->storeAttendanceDetail($validatedData);

    }

    /**
     * @throws Exception
     */
    public function getCoordinates($userId)
    {
        $user = $this->userRepo->findUserDetailById($userId);
        $branch = $this->branchRepository->findBranchDetailById($user->branch_id);

        return ['latitude' => $branch->branch_location_latitude, 'longitude' => $branch->branch_location_longitude];

    }
//    public function getEmployeeAttendanceSummaryOfTheMonth($filterParameter, array $select = ['*'], array $with = []): Collection|array
//    {
//        try {
//
//            if($filterParameter['date_in_bs']){
//                $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameter['year'], $filterParameter['month']);
//                $filterParameter['start_date'] = $dateInAD['start_date'] ?? null;
//                $filterParameter['end_date'] = $dateInAD['end_date'] ?? null;
//            }
//
//            $employeeMonthlyAttendance = [];
//
//
////            $select = ['id','attendance_date','user_id','check_in_at','check_out_at'];
////            $attendanceDetail = $this->attendanceRepo->getEmployeeAttendanceDetailOfTheMonth($filterParameter, $select);
////            foreach ($attendanceDetail as $key => $value){
////                $attendanceDate = $filterParameter['date_in_bs']
////                    ? AppHelper::dateInYmdFormatEngToNep($value->attendance_date)
////                    : $value->attendance_date;
////
////                $getDay = (int) explode('-', $attendanceDate)[2];
////                $employeeMonthlyAttendance[$getDay-1] = [
////                    'id' => $value->id,
////                    'user_id' => $value->user_id,
////                    'attendance_date' => $attendanceDate,
////                    'check_in_at' => $value->check_in_at,
////                    'check_out_at' => $value->check_out_at,
////                ];
////            }
//            $select = ['id','attendance_date','user_id','check_in_at','check_out_at'];
//            $attendanceDetail = $this->attendanceRepo->getEmployeeAttendanceDetailOfTheMonth($filterParameter, $select);
//
//            return AttendanceHelper::getMonthlyDetail($attendanceDetail, $filterParameter);
//        } catch (Exception $e) {
//            throw $e;
//        }
//    }

    /**
     * @throws Exception
     */
    public function getAttendanceExportData($startDate, $endDate,$filterData): Collection|array
    {
        $today = date('Y-m-d');
        if ($endDate > $today) {
            $endDate = $today;
        }

        $groupedByUser = [];

        $with = ['officeTime:id,shift_type,opening_time,closing_time'];
        $attendanceDetail = $this->attendanceRepo->getEmployeeAttendanceExport($startDate, $endDate, $with,$filterData);


        $allDates = [];
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            $allDates[] = $currentDate;
            $currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
        }


        foreach ($attendanceDetail as $attendance) {
            $userId = $attendance->user_id;
            if (!isset($groupedByUser[$userId])) {

                foreach ($allDates as $date) {
                    $groupedByUser[$userId][$date] = [
                        'data' => [],
                    ];
                }
            }
        }


        foreach ($attendanceDetail as $attendance) {
            $extraData = AttendanceHelper::getOverAndUnderTimeData($attendance);
            $userId = $attendance->user_id;
            $attendanceDate = $attendance->attendance_date;

            $attendanceData = [
                'id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'attendance_date' => $attendance->attendance_date,
                'check_in_at' => $attendance->check_in_at,
                'check_out_at' => $attendance->check_out_at,
                'check_in_latitude' => $attendance->check_in_latitude,
                'check_out_latitude' => $attendance->check_out_latitude,
                'check_in_longitude' => $attendance->check_in_longitude,
                'check_out_longitude' => $attendance->check_out_longitude,
                'attendance_status' => $attendance->attendance_status,
                'note' => $attendance->note,
                'edit_remark' => $attendance->edit_remark,
                'created_by' => $attendance->created_by,
                'created_at' => $attendance->created_at,
                'updated_at' => $attendance->updated_at,
                'check_in_type' => $attendance->check_in_type,
                'check_out_type' => $attendance->check_out_type,
                'worked_hour' => $attendance->worked_hour,
                'working_hour' => $extraData['workingHourMin'],
                'night_checkin' => $attendance->night_checkin,
                'night_checkout' => $attendance->night_checkout,
                'shift' => $attendance->officeTime->shift_type ?? '',
                'overtime' => $extraData['overTime'] ?? 0,
                'undertime' => isset($attendance->check_out_at) ? $extraData['underTime'] : 0,

            ];


            $groupedByUser[$userId][$attendanceDate]['data'][] = $attendanceData;
        }
        return $groupedByUser;
    }

    public function getAnnualAttendanceExportData($startDate, $endDate, $filterData)
    {
        $year = $filterData['year'];
        $months = AppHelper::getMonthsList();
        $monthKeys = array_keys($months); // e.g., [1,2,...,12]

        $users = $this->attendanceRepo->getAnnualAttendanceExport($startDate, $endDate, $filterData);

        if ($users->isEmpty()) {
            return collect();
        }

        // Fetch holidays & weekends
        $dates = ['start_date' => $startDate, 'end_date' => $endDate];
        $fullHolidays = $this->holidayService->getActiveHolidayList($dates);
        $fullWeekends = AppHelper::getMonthWeekendDates($dates);


        $monthData = [];
        $dateToMonthMap = [];
        $nonWorkingPerMonth = [];

        foreach ($monthKeys as $monthKey) {
            $bounds = AppHelper::getMonthDates($year, $monthKey);

            $holidays = array_filter($fullHolidays, fn($d) => strtotime($d) >= strtotime($bounds['start_date']) && strtotime($d) <= strtotime($bounds['end_date']));
            $weekends = array_filter($fullWeekends, fn($d) => strtotime($d) >= strtotime($bounds['start_date']) && strtotime($d) <= strtotime($bounds['end_date']));

            $nonWorkingDates = array_unique(array_merge($holidays, $weekends));
            $nonWorkingFlip = array_flip($nonWorkingDates);

            $totalDays = AppHelper::getTotalDaysInMonth($year, $monthKey);


            $monthData[$monthKey] = [
                'name'         => $months[$monthKey],
                'total_days'   => $totalDays,
                'holidays'     => count($holidays),
                'weekends'     => count($weekends),
                'working_days' => $totalDays - count($holidays) - count($weekends),
                'non_working'  => $nonWorkingFlip,
            ];

            // Fill date → month map
            $current = strtotime($bounds['start_date']);
            $end = strtotime($bounds['end_date']);
            while ($current <= $end) {
                $dateStr = date('Y-m-d', $current);
                $dateToMonthMap[$dateStr] = $monthKey;
                $current = strtotime('+1 day', $current);
            }
        }

        $result = collect();

        foreach ($users as $user) {
            // Initialize counters
            $presentCount = array_fill_keys($monthKeys, 0);
            $leaveCount   = array_fill_keys($monthKeys, 0);

            // Process attendance — O(1) per record
            foreach ($user->employeeAttendance as $attendance) {
                $date = $attendance->attendance_date;
                if (isset($dateToMonthMap[$date])) {
                    $presentCount[$dateToMonthMap[$date]]++;
                }
            }

            // Process approved leaves — efficient day-by-day counting
            foreach ($user->employeeLeaveRequests as $leave) {

                $from = $leave->leave_from;
                $to   = $leave->leave_to ?? $from;

                $current = strtotime($from);
                $end     = strtotime($to . ' +1 day');

                while ($current < $end) {
                    $dateStr = date('Y-m-d', $current);

                    if (isset($dateToMonthMap[$dateStr])) {
                        $mKey = $dateToMonthMap[$dateStr];
                        $mData = $monthData[$mKey];

                        // Only count leave if it's a working day
                        if (!isset($mData['non_working'][$dateStr])) {
                            $leaveCount[$mKey]++;
                        }
                    }

                    $current = strtotime('+1 day', $current);
                }
            }

            // Build result — one loop over months
            foreach ($monthKeys as $mKey) {
                $m = $monthData[$mKey];

                $present = $presentCount[$mKey];
                $leave   = $leaveCount[$mKey];
                $absent  = max(0, $m['working_days'] - $present - $leave);

                $result->push([
                    'user_id'      => $user->id,
                    'user_name'    => $user->name,
                    'month_key'    => $mKey,
                    'month_name'   => $m['name'],
                    'total_days'   => $m['total_days'],
                    'present'      => $present,
                    'leave'        => $leave,
                    'holidays'     => $m['holidays'],
                    'weekends'     => $m['weekends'],
                    'working_days' => $m['working_days'],
                    'absent'       => $absent,
                ]);
            }
        }

        return $result->groupBy('user_id');
    }


}
