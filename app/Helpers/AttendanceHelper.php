<?php

namespace App\Helpers;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\EmployeeSalary;
use App\Models\Holiday;
use App\Models\LeaveRequestMaster;
use App\Models\OfficeTime;
use App\Models\OverTimeEmployee;
use App\Models\TimeLeave;
use App\Models\UnderTimeSetting;
use App\Models\User;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceHelper
{
    const WEEK_DAY_IN_NEPALI = [
        '0' => array(
            'en' => 'Sunday',
            'en_short' => 'Sun',
            'np' => 'आइतबार',
            'np_short' => 'आइत',
        ),
        '1' => array(
            'en' => 'Monday',
            'en_short' => 'Mon',
            'np' => 'सोमबार',
            'np_short' => 'सोम',
        ),
        '2' => array(
            'en' => 'Tuesday',
            'en_short' => 'Tue',
            'np' => 'मंगलबार',
            'np_short' => 'मंगल',
        ),
        '3' => array(
            'en' => 'Wednesday',
            'en_short' => 'Wed',
            'np' => 'बुधबार',
            'np_short' => 'बुध',
        ),
        '4' => array(
            'en' => 'Thursday',
            'en_short' => 'Thur',
            'np' => 'बिहिबार',
            'np_short' => 'बिहि',
        ),
        '5' => array(
            'en' => 'Friday',
            'en_short' => 'Fri',
            'np' => 'शुक्रबार',
            'np_short' => 'शुक्र',
        ),
        '6' => array(
            'en' => 'Saturday',
            'en_short' => 'Sat',
            'np' => 'शनिबार',
            'np_short' => 'शनि',
        ),
    ];

    const WEEK_DAY = [
        'sunday' => 0,
        'saturday' => 6,
        'monday' => 2,
    ];


    public static function getStartOfWeekDate($currentDate): mixed
    {
        return $currentDate->startOfWeek(self::WEEK_DAY['sunday'])->format('Y-m-d');
    }

    public static function getEndOfWeekDate($currentDate): mixed
    {
        return $currentDate->endOfWeek(self::WEEK_DAY['saturday'])->format('Y-m-d');
    }

    public static function getWeekDayFromDate($date): string
    {
        $week = date('w', strtotime($date));
        return self::WEEK_DAY_IN_NEPALI[$week]['en'];
    }

    public static function getWeekDayInShortForm($date): string
    {
        $week = date('w', strtotime($date));
        return self::WEEK_DAY_IN_NEPALI[$week]['en_short'];
    }

    public static function changeTimeFormatForAttendanceView($time): string
    {

        $appTimeSetting = AppHelper::check24HoursTimeAppSetting();
        if ($appTimeSetting) {
            return date('H:i:s', strtotime($time));
        }
        return date('h:i A', strtotime($time));
    }

    public static function changeNightTimeFormatForAttendanceView($dateTime): string
    {
        $appTimeSetting = AppHelper::check24HoursTimeAppSetting();
        if ($appTimeSetting) {
            $formattedTime = date('Y-m-d H:i:s', strtotime($dateTime));
        } else {
            $formattedTime = date('Y-m-d h:i A', strtotime($dateTime));
        }
        return $formattedTime;
    }

    public static function changeNightAttendanceFormat($appTimeSetting, $dateTime): string
    {

        $isBsEnabled = AppHelper::ifDateInBsEnabled();
        $timestamp = strtotime($dateTime);

        if ($isBsEnabled) {
            $formattedDate = AppHelper::dateInYmdFormatEngToNep($dateTime);
            $formattedTime = $appTimeSetting ? date('H:i:s', $timestamp) : date('h:i A', $timestamp);

            return $formattedDate . ' ' . $formattedTime;
        } else {
            return $appTimeSetting ? date('Y-m-d H:i:s', $timestamp) : date('Y-m-d h:i A', $timestamp);
        }
    }

    public static function changeTimeFormatForAttendanceAdminView($appTimeSetting, $time): string
    {
        if ($appTimeSetting) {
            return date('H:i:s', strtotime($time));
        }
        return date('h:i:s A', strtotime($time));
    }

    public static function getWorkedHourInHourAndMinute($checkInTime, $checkOutTime): string
    {

        if (isset($checkInTime) && isset($checkOutTime)) {
            $checkIn = Carbon::createFromFormat('H:i:s', $checkInTime);
            $checkOut = Carbon::createFromFormat('H:i:s', $checkOutTime);

            $workedTimeInMinute = $checkIn->diffInMinutes($checkOut);

            return intdiv($workedTimeInMinute, 60) . ' hr ' . ($workedTimeInMinute % 60) . ' min';

        } else {
            return '0 hr 0 min';

        }
    }

    public static function getWorkedTimeInHourAndMinute($workedHour): string
    {
        if ($workedHour > 0) {

            $hours = floor($workedHour / 60);
            $minutes = $workedHour % 60;

            return $hours . 'hr ' . $minutes . 'm';
        } else {
            return 0;
        }

    }

    public static function isHolidayOrWeekendOnCurrentDate(): bool
    {
        $date = Carbon::today()->format('Y-m-d');
        $holiday = Holiday::whereDate('event_date', $date)->where('is_active',1)->count();

        if ($holiday == 0) {
            $weekDay = self::getWeekDayInNumber($date);
            $weekend = Company::whereJsonContains('weekend', $weekDay)->count();


            if ($weekend == 0) {
                return false;
            }
        }
        return true;
    }

    public static function isHolidayOrWeekend($startDate, $endDate): bool
    {

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($start->eq($end)) {
            $currentDate = $start->format('Y-m-d');

            // Check if current date is a holiday
            $holiday = Holiday::whereDate('event_date', $currentDate)->count();
            if ($holiday > 0) {
                return true; // It's a holiday
            }

            // Check if current date is a weekend
            $weekDay = self::getWeekDayInNumber($currentDate);
            $weekend = Company::whereJsonContains('weekend', $weekDay)->count();
            if ($weekend > 0) {
                return true; // It's a weekend
            }


        } else {
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($start, $interval, $end->modify('+1 day'));

            foreach ($period as $date) {
                $currentDate = $date->format('Y-m-d');

                // Check if current date is a holiday
                $holiday = Holiday::whereDate('event_date', $currentDate)->count();
                if ($holiday > 0) {
                    return true; // It's a holiday
                }

                // Check if current date is a weekend
                $weekDay = self::getWeekDayInNumber($currentDate);
                $weekend = Company::whereJsonContains('weekend', $weekDay)->count();
                if ($weekend > 0) {
                    return true; // It's a weekend
                }
            }
        }
        return false;


    }

    public static function getWeekDayInNumber($date): string
    {
        return date('w', strtotime($date));
    }

    public static function getTotalNumberOfDaysInSpecificMonth($month, $year): int
    {
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }

    public static function getEmployeeWorkedTimeInHourAndMinute($attendanceDetail): string
    {
        if (isset($attendanceDetail['check_out_at'])) {
            $productiveTimeInMin = Carbon::createFromFormat('H:i:s', $attendanceDetail['check_out_at'])->diffInMinutes($attendanceDetail['check_in_at']);
            return floor($productiveTimeInMin / 60) . 'hr ' . ($productiveTimeInMin - floor($productiveTimeInMin / 60) * 60) . 'm';
        } else {
            return '0 hr ' . '0 m';
        }

    }

    public static function getEmployeeWorkedTimeForNightShift($attendanceDetail): string
    {
        if (isset($attendanceDetail['night_checkout']) && isset($attendanceDetail['night_checkin'])) {
            $checkInTime = Carbon::parse($attendanceDetail['night_checkin']);
            $checkOutTime = Carbon::parse($attendanceDetail['night_checkout']);

            $productiveTimeInMin = $checkOutTime->diffInMinutes($checkInTime);

            $hours = floor($productiveTimeInMin / 60);
            $minutes = $productiveTimeInMin % 60;

            return $hours . 'hr ' . $minutes . 'm';
        } else {
            return '0 hr 0 m';
        }

    }

    public static function formattedAttendanceDate($isBsEnabled, $date, $changeEngToNep = true): string
    {
        if ($isBsEnabled && $changeEngToNep) {
            return AppHelper::dateInNepaliFormatEngToNep($date);
        }
        return date('d M Y (l)', strtotime($date));
    }

    public static function formattedAttendanceDateTime($isBsEnabled, $date, $changeEngToNep = true): string
    {
        if ($isBsEnabled && $changeEngToNep) {
            return AppHelper::dateInYmdFormatEngToNep($date) . ' ' . date('H:i:s', strtotime($date));
        }
        return date('d M Y H:i:s', strtotime($date));
    }


    public static function getHolidayOrLeaveDetail($date, $userId)
    {
        $leaveStatus = [
            'pending' => 'P',
            'rejected' => 'R',
            'approved' => 'A',
        ];

        if (Carbon::parse($date) <= Carbon::today()) {


            $holidayDetail = Holiday::whereDate('event_date', $date)->first();

            if ($holidayDetail) {
                return 'Holiday (' . (ucfirst($holidayDetail->event)) . ')';
            }

            $weekday = date('w', strtotime($date));

            $companyWeekend = Company::whereJsonContains('weekend', $weekday)->exists();

            if ($companyWeekend) {
                return 'Weekend';
            }

            $leaveDetail = LeaveRequestMaster::where('requested_by', $userId)
                ->where('status', '=', 'approved')
                ->where(function ($query) use ($date) {
                    $query->whereRaw('DATE(leave_from) <= ?', [$date])
                        ->whereRaw('DATE(leave_to) >= ?', [$date]);
                })
                ->where('early_exit', 0)
                ->first();

            if ($leaveDetail) {
                return 'Leave (' . ($leaveStatus[$leaveDetail['status']]) . ')';
            } else {
                return 'Absent';
            }

        }
    }

    public static function getHolidayOrWeekendDetailForAttendance($date)
    {

        $returnValue = '';
        if (Carbon::parse($date) <= Carbon::today()) {


            $holidayDetail = Holiday::whereDate('event_date', $date)->first();

            if ($holidayDetail) {
               $returnValue = 'Holiday (' . (ucfirst($holidayDetail->event)) . ')';
            }else{
                $weekday = date('w', strtotime($date));

                $companyWeekend = Company::whereJsonContains('weekend', $weekday)->exists();

                if ($companyWeekend) {
                    $returnValue = 'Weekend';
                }
            }
        }
       return $returnValue;
    }

    public static function getMonthlyDetail($userId, $date_in_bs, $year, $month): mixed
    {
        $today = date('Y-m-d');
        $isCurrentMonth = false;
        $presentCount = 0;
        $timeLeaves = 0;
        $holidayCount = 0;
        $absentCount = 0;
        $leaveCount = 0;
        $halfLeaveDays = 0;
        $workedHours = 0;
        $weekendCount = 0;
        $totalWorkedHours = '';
        $totalWorkingHours = '';


        if ($date_in_bs) {
            $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($year, $month);
            $startDate = date('Y-m-d', strtotime($dateInAD['start_date'])) ?? null;
            $endDate = date('Y-m-d', strtotime($dateInAD['end_date'])) ?? null;
            $days = AppHelper::getTotalDaysInNepaliMonth($year, $month);

            $currentMonthYear = AppHelper::getCurrentNepaliYearMonth();
            if ($currentMonthYear['year'] == $year && $currentMonthYear['month'] == $month) {
                $isCurrentMonth = true;
            }

        } else {

            $days = AttendanceHelper::getTotalNumberOfDaysInSpecificMonth($month, $year);
            $firstDayOfMonth = Carbon::create($year, $month, 1)->startOfDay();
            $startDate = date('Y-m-d', strtotime($firstDayOfMonth));
            $endDate = date('Y-m-d', strtotime($firstDayOfMonth->endOfMonth()));

            if (date('Y') == $year && date('m' == $month)) {
                $isCurrentMonth = true;
            }
        }


        if ($isCurrentMonth) {
            $endDate = $today;
        }

        if ($startDate <= $endDate) {
            // get weekend counts
            $weekendCount = self::countWeekends($startDate, $endDate);

            $leaveDays = LeaveRequestMaster::where('status', '=', 'approved')
                ->where('requested_by', $userId)
                ->whereBetween('leave_from', [$startDate, $endDate])
                ->whereBetween('leave_to', [$startDate, $endDate])
                ->where('leave_for','=','full_day')
                ->where('early_exit', 0)
                ->sum('no_of_days');

            $halfLeaveDays = LeaveRequestMaster::where('status', '=', 'approved')
                ->where('requested_by', $userId)
                ->whereBetween('leave_from', [$startDate, $endDate])
                ->whereBetween('leave_to', [$startDate, $endDate])
                ->where('leave_for','=','half_day')
                ->where('early_exit', 0)
                ->sum('no_of_days');

            $timeLeaves = TimeLeave::where('status', '=', 'approved')
                ->where('requested_by', $userId)
                ->whereBetween('issue_date', [$startDate, $endDate])
                ->count();

            $leaveCount = intval($leaveDays);

//            $worked_time = Attendance::select(
//                DB::raw("COUNT(attendances.attendance_date) as present_days"),
//                DB::raw("SUM(
//        CASE
//            WHEN over_time_settings.is_active = 1
//                AND (
//                    TIME_TO_SEC(TIMEDIFF(attendances.check_out_at, attendances.check_in_at))/60 -
//                    TIME_TO_SEC(TIMEDIFF(COALESCE(office_times.closing_time, '00:00:00'), COALESCE(office_times.opening_time, '00:00:00')))/60
//                ) >= (over_time_settings.valid_after_hour * 60)
//            THEN
//                LEAST(
//                    (
//                        TIME_TO_SEC(TIMEDIFF(attendances.check_out_at, attendances.check_in_at))/60 -
//                        TIME_TO_SEC(TIMEDIFF(COALESCE(office_times.closing_time, '00:00:00'), COALESCE(office_times.opening_time, '00:00:00')))/60
//                    ),
//                    over_time_settings.max_daily_ot_hours * 60
//                )
//            ELSE 0
//        END
//    ) as total_overtime"),
//                DB::raw("SUM(
//        CASE
//            WHEN under_time_settings.is_active = 1
//                AND (
//                    TIME_TO_SEC(TIMEDIFF(attendances.check_out_at, attendances.check_in_at))/60 <
//                    (
//                        TIME_TO_SEC(TIMEDIFF(COALESCE(office_times.closing_time, '00:00:00'), COALESCE(office_times.opening_time, '00:00:00')))/60 -
//                        under_time_settings.applied_after_minutes
//                    )
//                )
//            THEN
//                (
//                    TIME_TO_SEC(TIMEDIFF(COALESCE(office_times.closing_time, '00:00:00'), COALESCE(office_times.opening_time, '00:00:00')))/60 -
//                    under_time_settings.applied_after_minutes
//                ) -
//                TIME_TO_SEC(TIMEDIFF(attendances.check_out_at, attendances.check_in_at))/60
//            ELSE 0
//        END
//    ) as total_undertime"),
//                DB::raw("SUM(
//        CASE
//            WHEN attendances.worked_hour IS NOT NULL
//            THEN attendances.worked_hour
//            ELSE TIME_TO_SEC(TIMEDIFF(attendances.check_out_at, attendances.check_in_at))/60
//        END
//    ) AS total_minutes")
//            )
//                ->leftJoin('users', 'attendances.user_id', '=', 'users.id')
//                ->leftJoin('office_times', function ($join) {
//                    $join->on('users.office_time_id', '=', 'office_times.id')
//                        ->where('office_times.is_active', '=', 1);
//                })
//                ->leftJoin('over_time_employees', 'attendances.user_id', '=', 'over_time_employees.employee_id')
//                ->leftJoin('over_time_settings', function ($join) {
//                    $join->on('over_time_employees.over_time_setting_id', '=', 'over_time_settings.id')
//                        ->where('over_time_settings.is_active', '=', 1);
//                })
//                ->leftJoin('under_time_settings', function ($join) {
//                    $join->where('under_time_settings.is_active', '=', 1);
//                })
//                ->where('attendances.attendance_status', 1)
//                ->whereNotNull('attendances.check_in_at')
//                ->whereNotNull('attendances.check_out_at')
//                ->whereBetween('attendances.attendance_date', [$startDate, $endDate])
//                ->where('attendances.user_id', $userId)
//                ->get();

            $worked_time = Attendance::selectRaw('
                    COUNT(DISTINCT attendance_date) AS present_days,
                    SUM(overtime) AS total_overtime,
                    SUM(undertime) AS total_undertime,
                    SUM(worked_hour) AS total_minutes
                ')
                ->where('attendance_status', 1)
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->where('user_id', $userId)
                ->get();


            $workedHours = $worked_time[0]->total_minutes;
            $presentCount = $worked_time[0]->present_days;


            $weekendDays = json_decode(Company::pluck('weekend')->first(), true);

            if (!is_array($weekendDays) || empty($weekendDays)) {
                $weekendDays = [];
            }

            $conditions = collect($weekendDays)
                ->map(fn($day) => "DAYOFWEEK(event_date) = " . ((int)$day + 1))
                ->implode(' OR ');

            $holidayCounts = Holiday::whereBetween('event_date', [$startDate, $endDate])
                ->selectRaw("
                    COUNT(*) AS total_count,
                    SUM(CASE WHEN $conditions THEN 1 ELSE 0 END) AS weekend_count
                ")
                ->first();


            $holidayCount = $holidayCounts->total_count ?? 0;
            $weekendHolidayCount = $holidayCounts->weekend_count ?? 0;

            $weekendCount = $weekendCount - $weekendHolidayCount;

            $shift = User::select(
                DB::raw("
                    TIME_FORMAT(
                        TIMEDIFF(
                            CASE
                                WHEN office_times.shift_type = 'night'
                                THEN ADDTIME(office_times.closing_time, '24:00:00')
                                ELSE office_times.closing_time
                            END,
                            office_times.opening_time
                        ),
                        '%l'
                        ) as hours
                    ")
            )
                ->leftJoin('office_times', function ($join) {
                    $join->on('users.office_time_id', '=', 'office_times.id')
                        ->where('office_times.is_active', '=', 1);
                })
                ->where('users.id', $userId)
                ->first();

            $workingHours = $shift['hours'];
            $totalWorkingHours = $workingHours * ($days - ($weekendCount + $holidayCount)) . 'h';

            $totalWorkedHours = floor($workedHours / 60) . 'h ' . round(($workedHours - floor($workedHours / 60) * 60)) . 'm';


            //get days diff for current month
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            $diffInDays = $start->diffInDays($end) + 1;

            $absentCount = $diffInDays - ($presentCount + $weekendCount + $leaveCount + $holidayCount);

        }

        return array(
            'totalDays' => $days,
            'totalWeekend' => $weekendCount,
            'totalPresent' => $presentCount,
            'totalHoliday' => $holidayCount,
            'totalAbsent' => $absentCount,
            'totalTimeLeave' => $timeLeaves,
            'totalLeave' => $leaveCount,
            'halfLeaveDays' => $halfLeaveDays,
            'totalWorkedHours' => $totalWorkedHours,
            'totalWorkedHourInMin' => $workedHours,
            'totalWorkingHours' => $totalWorkingHours,
            'totalOverTime' => isset($worked_time[0]) ? $worked_time[0]->total_overtime : 0,
            'totalUnderTime' => isset($worked_time[0]) ? $worked_time[0]->total_undertime : 0,
        );
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return int
     */
    public static function countWeekends($startDate, $endDate): int
    {
        $companyWeekend = Company::pluck('weekend')->first();
        $weekendDays = json_decode($companyWeekend, true);

        if (!is_array($weekendDays)) {
            $weekendDays = [];
        }

        $start = date('Y-m-d', strtotime($startDate));
        $end = date('Y-m-d', strtotime($endDate));

        $weekendCount = 0;

        while ($start <= $end) {
            $weekday = date('w', strtotime($start));

            if (in_array($weekday, $weekendDays)) {
                $weekendCount++;
            }

            $start = date('Y-m-d', strtotime("+1 day", strtotime($start)));
        }

        return $weekendCount;
    }

    public static function holidayAndWeekendCount($startDate, $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $holidays = Holiday::whereBetween('event_date', [$start, $end])
            ->count();

        $weekends = self::countWeekends($start, $end);

        return $holidays + $weekends;
    }


    /**
     * @param $employeeId
     * @return mixed
     */
    public static function checkEmployeeSalary($employeeId): mixed
    {
        return EmployeeSalary::where('employee_id', $employeeId)->count();
    }


    public static function getWeeklyDetail($userId, $date_in_bs, $start_date, $end_date): mixed
    {
        $days = 7;


        if ($date_in_bs) {

            $startDate = date('Y-m-d', strtotime($start_date)) ?? null;
            $endDate = date('Y-m-d', strtotime($end_date)) ?? null;

        } else {


            $startDate = $start_date;
            $endDate = $end_date;
        }

        // get weekend counts
        $weekendCount = self::countWeekends($startDate, $endDate);

        $leaveDays = LeaveRequestMaster::where('status', '=', 'approved')
            ->where('requested_by', $userId)
            ->whereBetween('leave_from', [$startDate, $endDate])
            ->whereBetween('leave_to', [$startDate, $endDate])
            ->where('early_exit', 0)
            ->sum('no_of_days');

        $leaveCount = intval($leaveDays);

        $worked_time = Attendance::selectRaw('
                    COUNT(DISTINCT attendance_date) AS present_days,
                    SUM(overtime) AS total_overtime,
                    SUM(undertime) AS total_undertime,
                    SUM(worked_hour) AS total_minutes
                ')
            ->where('attendance_status', 1)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->where('user_id', $userId)
            ->get();
//        $worked_time = Attendance::select(
//            DB::raw("COUNT(attendances.attendance_date) as present_days"),
//            DB::raw("SUM(
//        CASE
//            WHEN over_time_settings.is_active = 1
//                AND (
//                    TIME_TO_SEC(TIMEDIFF(attendances.check_out_at, attendances.check_in_at))/60 -
//                    TIME_TO_SEC(TIMEDIFF(office_times.closing_time, office_times.opening_time))/60
//                ) >= (over_time_settings.valid_after_hour * 60)
//            THEN
//                LEAST(
//                    (
//                        TIME_TO_SEC(TIMEDIFF(attendances.check_out_at, attendances.check_in_at))/60 -
//                        TIME_TO_SEC(TIMEDIFF(office_times.closing_time, office_times.opening_time))/60
//                    ),
//                    over_time_settings.max_daily_ot_hours * 60
//                )
//            ELSE 0
//        END
//    ) as total_overtime"),
//            DB::raw("SUM(
//        CASE
//            WHEN under_time_settings.is_active = 1
//                AND (
//                    TIME_TO_SEC(TIMEDIFF(attendances.check_out_at, attendances.check_in_at))/60 <
//                    (
//                        TIME_TO_SEC(TIMEDIFF(office_times.closing_time, office_times.opening_time))/60 -
//                        under_time_settings.applied_after_minutes
//                    )
//                )
//            THEN
//                (
//                    TIME_TO_SEC(TIMEDIFF(office_times.closing_time, office_times.opening_time))/60 -
//                    under_time_settings.applied_after_minutes
//                ) -
//                TIME_TO_SEC(TIMEDIFF(attendances.check_out_at, attendances.check_in_at))/60
//            ELSE 0
//        END
//    ) as total_undertime"),
//            DB::raw("SUM(
//        CASE
//            WHEN attendances.worked_hour IS NOT NULL
//            THEN attendances.worked_hour
//            ELSE TIME_TO_SEC(TIMEDIFF(attendances.check_out_at, attendances.check_in_at))/60
//        END
//    ) AS total_minutes")
//        )
//            ->leftJoin('users', 'attendances.user_id', '=', 'users.id')
//            ->leftJoin('office_times', function ($join) {
//                $join->on('users.office_time_id', '=', 'office_times.id')
//                    ->where('office_times.is_active', '=', 1);
//            })
//            ->leftJoin('over_time_employees', 'attendances.user_id', '=', 'over_time_employees.employee_id')
//            ->leftJoin('over_time_settings', function ($join) {
//                $join->on('over_time_employees.over_time_setting_id', '=', 'over_time_settings.id')
//                    ->where('over_time_settings.is_active', '=', 1);
//            })
//            ->leftJoin('under_time_settings', function ($join) {
//                $join->where('under_time_settings.is_active', '=', 1);
//            })
//            ->where('attendances.attendance_status', 1)
//            ->whereNotNull('attendances.check_in_at')
//            ->whereNotNull('attendances.check_out_at')
//            ->whereBetween('attendances.attendance_date', [$startDate, $endDate])
//            ->where('attendances.user_id', $userId)
//            ->get();

        $WorkedHours = $worked_time[0]->total_minutes;
        $presentCount = $worked_time[0]->present_days;

        $weekendDays = json_decode(Company::pluck('weekend')->first(), true);

        if (!is_array($weekendDays) || empty($weekendDays)) {
            $weekendDays = [];
        }

        $conditions = collect($weekendDays)
            ->map(fn($day) => "DAYOFWEEK(event_date) = " . ((int)$day + 1))
            ->implode(' OR ');

        $holidayCounts = Holiday::whereBetween('event_date', [$startDate, $endDate])
            ->selectRaw("
                    COUNT(*) AS total_count,
                    SUM(CASE WHEN $conditions THEN 1 ELSE 0 END) AS weekend_count
                ")
            ->first();

        $holidayCount = $holidayCounts->total_count ?? 0;
        $weekendHolidayCount = $holidayCounts->weekend_count ?? 0;

        $weekendCount = $weekendCount - $weekendHolidayCount;

        $totalWorkedHours = floor($WorkedHours / 60) . 'h ' . round(($WorkedHours - floor($WorkedHours / 60) * 60)) . 'm';

        //get days diff for current month
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $diffInDays = $start->diffInDays($end) + 1;

        $absentCount = $diffInDays - ($presentCount + $weekendCount + $leaveCount + $holidayCount);

        return array(
            'totalDays' => $days,
            'totalWeekend' => $weekendCount,
            'totalPresent' => $presentCount,
            'totalHoliday' => $holidayCount,
            'totalAbsent' => $absentCount,
            'totalLeave' => $leaveCount,
            'totalWorkedHours' => $totalWorkedHours,
            'totalWorkedHourInMin' => $WorkedHours,
            'totalOverTime' => $worked_time[0]->total_overtime,
            'totalUnderTime' => $worked_time[0]->total_undertime,
        );
    }

    /**
     * @throws Exception
     */
    public static function calculateNightWorkedHour($checkOutDateTime, $checkInDateTime, $employeeId): array
    {

        $shift = User::select(
            DB::raw("
                TIMESTAMPDIFF(
                    MINUTE,
                    office_times.opening_time,
                    CASE
                        WHEN office_times.shift_type = 'night'
                        THEN ADDTIME(office_times.closing_time, '24:00:00')
                        ELSE office_times.closing_time
                    END
                ) as minutes
            ")
        )
            ->leftJoin('office_times', function ($join) {
                $join->on('users.office_time_id', '=', 'office_times.id')
                    ->where('office_times.is_active', '=', 1);
            })
            ->where('users.id', $employeeId)
            ->first();

        $workingTime = $shift['minutes'];

        $checkIn = Carbon::parse($checkInDateTime);
        $checkOut = Carbon::parse($checkOutDateTime);
        $workedTime = $checkOut->diffInMinutes($checkIn);

        $extraWorkedTime = 0;

        if (($workingTime > 0) && ($workedTime > $workingTime)) {
            $extraWorkedTime = $workedTime - $workingTime;
        }

        // calculate underTime
        $workTimeDeficiency = 0;
        if ($workedTime < $workingTime) {
            $workTimeDeficiency = $workingTime - $workedTime;
        }

        return [
            'workedHours' => round($workedTime, 2),
            'overtime' => isset($extraWorkedTime) ? round($extraWorkedTime, 2) : 0,
            'undertime' => isset($workTimeDeficiency) ? round($workTimeDeficiency, 2) : 0,
        ];
    }

    /**
     * @throws Exception
     */
    public static function calculateWorkedHour($checkOutTime, $checkInTime, $employeeId): array
    {
        $shift = User::
        select(
            'office_times.closing_time', 'office_times.opening_time'
        )
            ->leftJoin('office_times', function ($join) {
                $join->on('users.office_time_id', '=', 'office_times.id')
                    ->where('office_times.is_active', '=', 1);
            })
            ->where('users.id', $employeeId)->first();


        $workingTime = self::calculateTime($shift['closing_time'], $shift['opening_time']);


        $workedTime = self::calculateTime($checkOutTime, $checkInTime);


        $extraWorkedTime = 0;

        if (($workingTime > 0) && ($workedTime > $workingTime)) {
            $extraWorkedTime = $workedTime - $workingTime;
        }


        // calculate underTime

        $workTimeDeficiency = 0;

        if ($workedTime < $workingTime) {
            $workTimeDeficiency = $workingTime - $workedTime;
        }

        return [
            'workedHours' => round($workedTime, 2),
            'overtime' => isset($extraWorkedTime) ? round($extraWorkedTime, 2) : 0,
            'undertime' => isset($workTimeDeficiency) ? round($workTimeDeficiency, 2) : 0,
        ];
    }

    /**
     * @throws Exception
     */
    public static function calculateTime($outTime, $inTime): float|int
    {
        $dateTime1 = new DateTime($outTime);
        $dateTime2 = new DateTime($inTime);

        // Calculate the difference between the two DateTime objects
        $interval = $dateTime1->diff($dateTime2);

        // Convert the difference to minutes
        return ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
    }

    public static function payslipDate($date): string
    {

        $isBsEnabled = AppHelper::ifDateInBsEnabled();

        if ($isBsEnabled) {
            return AppHelper::dateInYmdFormatEngToNep($date);
        }
        return date('Y-m-d', strtotime($date));
    }

    public static function payslipDuration($startDate, $endDate): string
    {
        $isBsEnabled = AppHelper::ifDateInBsEnabled();


        if ($isBsEnabled) {
            $fromMonth = AppHelper::getMonthYear($startDate);
            $toMonth = AppHelper::getMonthYear($endDate);

            $sMonth = explode(' ', $fromMonth);
            $eMonth = explode(' ', $fromMonth);
            if ($fromMonth != $toMonth) {
                $duration = $sMonth[0] . '/' . $eMonth[0] . ' ' . $eMonth[1];
            } else {
                $duration = $fromMonth;
            }
        } else {
            $fromMonth = date('F', strtotime($startDate));
            $toMonth = date('F', strtotime($endDate));

            if ($fromMonth != $toMonth) {
                $duration = $fromMonth . '/' . $toMonth . ' ' . date('Y', strtotime($endDate));
            } else {
                $duration = $fromMonth . ' ' . date('Y', strtotime($startDate));
            }

        }
        return $duration;
    }

    public static function paidDate($date): string
    {
        $isBsEnabled = AppHelper::ifDateInBsEnabled();

        if ($isBsEnabled) {
            return AppHelper::formatDateForView($date);
        }
        return date('d/m/Y', strtotime($date));
    }

    public static function weekDay($date): string
    {
        if (AppHelper::ifDateInBsEnabled()) {
            $date = AppHelper::nepToEngDateInYmdFormat($date);
        }
        return date('D', strtotime($date));
    }

    /**
     * @throws Exception
     */
    public static function checkNightShiftCheckOut($userId): string
    {
        $shift = AppHelper::getUserShift($userId);
        $yesterday = Carbon::now()->subDay();
        $userYesterdayCheckInDetail = Attendance::select('id', 'user_id', 'night_checkin', 'night_checkout')
            ->where('user_id', $userId)
            ->where('attendance_date', $yesterday->format('Y-m-d'))
            ->where('office_time_id', $shift->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $userTodayCheckInDetail = Attendance::select('id', 'user_id', 'night_checkin', 'night_checkout')
            ->where('user_id', $userId)
            ->where('attendance_date', date('Y-m-d'))
            ->where('office_time_id', $shift->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (isset($userYesterdayCheckInDetail->night_checkout)) {
            $currentDate = date('Y-m-d');

        } else {
            $currentDate = Carbon::now()->subDay()->format('Y-m-d');
        }
        // Current date
        $currentTime = strtotime(now());

        $startTime = strtotime("$currentDate {$shift->opening_time}");
        $endTime = strtotime("$currentDate {$shift->closing_time}");

        // If end time is earlier than start time, it means the shift crosses midnight
        if ($endTime <= $startTime) {
            $endTime = strtotime("$currentDate {$shift->closing_time} +1 day");
        }

        $checkInStartTime = $startTime - ($shift->checkin_before * 60);
        $checkInEndTime = $startTime + ($shift->checkin_after * 60);
        $checkOutStartTime = $endTime - ($shift->checkout_before * 60);
        $checkOutEndTime = $endTime + ($shift->checkout_after * 60);


        if ((isset($userYesterdayCheckInDetail) && !isset($userYesterdayCheckInDetail->night_checkout)) && ($currentTime >= $checkOutStartTime || $currentTime <= $checkOutEndTime)) {
            return 'checkout';
        } elseif (!isset($userTodayCheckInDetail) && (($currentTime >= $checkInStartTime) || ($currentTime <= $checkInEndTime))) {
            return 'checkin';
        } elseif (isset($userTodayCheckInDetail) && !isset($userTodayCheckInDetail->night_checkout) && (($currentTime < $checkOutStartTime) || ($currentTime > $checkOutEndTime))) {
            return 'checkout_error';
        } else {
            return '';
        }

    }

    public static function getOverAndUnderTimeData($attendance)
    {
        $workingHourMin = 0;
        $underTime = 0;
        $overTime = 0;
        $isUnderTime = false;
        $isOverTime = false;

        $shift = User::
        select(
            'office_times.closing_time', 'office_times.opening_time'
        )
            ->leftJoin('office_times', function ($join) {
                $join->on('users.office_time_id', '=', 'office_times.id')
                    ->where('office_times.is_active', '=', 1);
            })
            ->where('users.id', $attendance->user_id)->first();


        $workingHourMin = self::calculateTime($shift['closing_time'], $shift['opening_time']);


        $workedHourMin = $attendance->worked_hour;
        $difference = $workingHourMin - $workedHourMin;

        if ($difference > 0) {
            $isUnderTime = isset($attendance->check_out_at);
            $underTime = $difference;
        } elseif ($difference < 0) {
            $isOverTime = true;
            $overTime = abs($difference);
        }

        return [
            'overTime' => (int)$overTime,
            'underTime' => (int)$underTime,
            'isUnderTime' => $isUnderTime,
            'isOverTime' => $isOverTime,
            'workingHourMin' => (int)$workingHourMin,
        ];

    }

    public static function getWorkingHours($employeeId){
        $shift = User::
        select(
            'office_times.closing_time', 'office_times.opening_time'
        )
            ->leftJoin('office_times', function ($join) {
                $join->on('users.office_time_id', '=', 'office_times.id')
                    ->where('office_times.is_active', '=', 1);
            })
            ->where('users.id', $employeeId)->first();


        return self::calculateTime($shift['closing_time'], $shift['opening_time']);
    }
}
