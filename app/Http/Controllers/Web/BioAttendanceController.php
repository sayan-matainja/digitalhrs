<?php

namespace App\Http\Controllers\Web;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Device;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class BioAttendanceController extends Controller
{


    public function handleDevice(Request $request)
    {

        // --- Handle Handshake (GET) ---
        if ($request->isMethod('get')) {
            return $this->handshake($request);
        }

        // --- Handle Data Push (POST) ---
        if ($request->isMethod('post')) {
            return $this->receiveRecords($request);
        }

        return response("OK", 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');

    }

    private function handshake(Request $request)
    {
        $serialNumber = $request->input('SN');

        if (empty($serialNumber)) {
            return response("ERROR: Missing serial number", 200, ['Content-Type' => 'text/plain']);
        }

        $device = Device::where('serial_number', $serialNumber)->first();
        if (!$device) {
            return response("ERROR: Device not found", 200, ['Content-Type' => 'text/plain']);
        }

        $updateData = [
            'last_online' => now(),
            'status' => 'working',
            'ip_address' => $request->getClientIp() ?? 'unknown',
        ];
        $device->update($updateData);

        // Handle time sync request
        if ($request->input('type') === 'time') {
            $time = now()->format('Y-m-d H:i:s'); // Correct format
            return response("Time={$time}", 200, ['Content-Type' => 'text/plain']);
        }


        $r = "GET OPTION FROM: {$serialNumber}\r\n" .
            "ErrorDelay=60\r\n" .
            "Delay=30\r\n" .
            "TransTimes=00:00;14:05\r\n" .
            "TransInterval=1\r\n" .
            "TransFlag=1111111000\r\n" .
            "Realtime=1\r\n" .
            "Encrypt=0\r\n" .
            "TimeZone=+08:00\r\n" .
            "Timeout=60\r\n" .
            "SyncTime=1\r\n" .
            "ServerVer=3.4.1 2010-06-07\r\n" .
            "ATTLOGStamp=" . now()->format('Y-m-d H:i:s') . "\r\n";

        return response($r, 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');

    }


    public function receiveRecords(Request $request)
    {

        $content['url'] = json_encode($request->all());
        $content['data'] = $request->getContent();
        Log::info('finger logs '. json_encode($content));
        DB::table('biometric_finger_logs')->insert($content);

        try {
            $arr = preg_split('/\\r\\n|\\r|,|\\n/', $request->getContent());
            $tot = 0;

            // Explicit handling for different tables
            $table = $request->input('table');

            if ($table == 'OPERLOG') {
                // Operation log processing
                foreach ($arr as $rey) {
                    if (isset($rey) && !empty(trim($rey))) {
                        $tot++;
                    }
                }
                return "OK: {$tot}";
            } elseif ($table == 'ATTLOG') {
                // Explicit ATTLOG handling
                foreach ($arr as $rey) {
                    if (empty(trim($rey))) {
                        continue;
                    }
                    $data = explode("\t", trim($rey));

                    $q['sn'] = $request->input('SN');
                    $q['table'] = $table;
                    $q['stamp'] = $request->input('Stamp');
                    $q['employee_id'] = $data[0];
                    $q['timestamp'] = $data[1];
                    $q['attendance_status'] = $this->validateAndFormatInteger($data[2] ?? null);
                    $q['data_receive_status'] = $this->validateAndFormatInteger($data[3] ?? null);
                    $q['workspace_id'] = $this->validateAndFormatInteger($data[4] ?? null);
                    $q['status4'] = $this->validateAndFormatInteger($data[5] ?? null);
                    $q['status5'] = $this->validateAndFormatInteger($data[6] ?? null);
                    $q['created_at'] = now();
                    $q['updated_at'] = now();
                    DB::table('biometric_attendance_logs')->insert($q);
                    $tot++;

                    $employeeCode = $data[0];
                    $attendanceTime = $data[1];
                    $status = (int) ($data[2] ?? 0);

                    // Validate employee code
                    $prefix = AppHelper::getEmployeeCodePrefix();
                    $code = $prefix . '-' . str_pad($employeeCode, 5, '0', STR_PAD_LEFT);

                    $user = User::where('employee_code', $code)->first();
                    if (!$user) {
                        Log::warning("User not found for employeeCode: {$employeeCode}");
                        continue;
                    }

                    $multipleAttendance = AppHelper::getAttendanceLimit();
                    $nightShift = AppHelper::isOnNightShift($user->id);

                    $attendanceDate = date('Y-m-d', strtotime($attendanceTime));
                    $timePart = date('H:i:s', strtotime($attendanceTime));
                    $coordinate = $this->getCoordinates($user->branch_id);

                    // Only handle check-in (0) and check-out (1) with the new logic; others remain as-is
                    if (in_array($status, [0, 1])) {
                        if ($nightShift) {
                            $this->handleSingleNightAttendanceZKTeco($user, $status, $attendanceDate, $timePart, $coordinate);
                        } else {
                            if ($multipleAttendance > 1) {
                                $this->handleMultipleAttendanceZKTeco($user, $status, $attendanceDate, $timePart, $coordinate, $multipleAttendance);
                            } else {
                                $this->handleSingleAttendanceZKTeco($user, $status, $attendanceDate, $timePart, $coordinate);
                            }
                        }
                    } else {
                        // Existing logic for other statuses (lunch, overtime)
                        switch ($status) {
                            case 2:  // Lunch-in
                                Log::info("Lunch-in for user {$employeeCode}");
                                    //            if ($todayAttendance && !$todayAttendance->lunch_in_at) {
                                    //                $todayAttendance->update([
                                    //                    'lunch_in_at' => $timePart,
                                    //                    'lunch_in_type' => EmployeeAttendanceTypeEnum::biometric->value,
                                    //                    'lunch_in_latitude' => $coordinate['latitude'],
                                    //                    'lunch_in_longitude' => $coordinate['longitude'],
                                    //                ]);
                                    //            }
                                break;

                            case 3:  // Lunch-out
                                Log::info("Lunch-out for user {$employeeCode}");
                                //            if ($todayAttendance && !$todayAttendance->lunch_out_at) {
                                //                $todayAttendance->update([
                                //                    'lunch_out_at' => $timePart,
                                //                    'lunch_out_type' => EmployeeAttendanceTypeEnum::biometric->value,
                                //                    'lunch_out_latitude' => $coordinate['latitude'],
                                //                    'lunch_out_longitude' => $coordinate['longitude'],
                                //                ]);
                                //            }
                                break;

                            case 4:  // Overtime-in
                                Log::info("Overtime-in for user {$employeeCode}");
                                //            if ($todayAttendance && !$todayAttendance->overtime_in_at) {
                                //                $todayAttendance->update([
                                //                    'overtime_in_at' => $timePart,
                                //                    'overtime_in_type' => EmployeeAttendanceTypeEnum::biometric->value,
                                //                    'overtime_in_latitude' => $coordinate['latitude'],
                                //                    'overtime_in_longitude' => $coordinate['longitude'],
                                //                ]);
                                //            }
                                break;

                            case 5:  // Overtime-out
                                Log::info("Overtime-out for user {$employeeCode}");
                                //            if ($todayAttendance && !$todayAttendance->overtime_out_at) {
                                //                $todayAttendance->update([
                                //                    'overtime_out_at' => $timePart,
                                //                    'overtime_out_type' => EmployeeAttendanceTypeEnum::biometric->value,
                                //                    'overtime_out_latitude' => $coordinate['latitude'],
                                //                    'overtime_out_longitude' => $coordinate['longitude'],
                                //                ]);
                                //                // Optionally recalculate overtime here if needed
                                //            }
                                break;

                            default:
                                Log::warning("Invalid status {$status} for user {$employeeCode}");
                                break;
                        }
                    }

                    // Update device last stamps after processing (for ATTLOG)
                    $device = Device::where('serial_number', $request->input('SN'))->first();
                    if ($device) {
                        $device->update([
                            'last_stamp' => $request->input('Stamp') ?? now()->timestamp,
                        ]);
                    }

                }
            } else {
                Log::warning("Unhandled table: {$table}, content: " . $request->getContent());
                return "OK"; // Return OK so device doesn't retry endlessly
            }

            return "OK: {$tot}";
        } catch (Throwable $e) {
            report($e);
            return "ERROR: {$tot}\n";
        }
    }


    /**
     * @param $value
     * @return int|null
     */
    private function validateAndFormatInteger($value): ?int
    {
        return isset($value) && $value !== '' ? (int)$value : null;
    }


    /**
     * @param $branchId
     * @return array
     */
    public function getCoordinates($branchId): array
    {
        $branch = Branch::where('id',$branchId)->first();

        return ['latitude' => $branch->branch_location_latitude, 'longitude' => $branch->branch_location_longitude];

    }


    /**
     * @throws \Exception
     */
    private function handleMultipleAttendanceZKTeco($user, $status, $attendanceDate, $timePart, $coordinate, $multipleAttendance)
    {
        $select = ['id', 'user_id', 'check_out_at', 'check_in_at'];
        $userTodayCheckInDetail = Attendance::select($select)
            ->where('user_id',$user->id)
            ->where('attendance_date',$attendanceDate)
            ->orderBy('created_at','desc')
            ->first();

        $attendanceDataCount =  Attendance::where('user_id',$user->id)
            ->where('attendance_date',$attendanceDate)
            ->whereNotNull('check_in_at')
            ->whereNotNull('check_out_at')
            ->count();


        if (($multipleAttendance * 2) / 2 == $attendanceDataCount) {
            Log::warning("Multiple checkout warning for user {$user->employee_code}");
            return;
        }

        if ($userTodayCheckInDetail) {
            $this->processExistingAttendanceZKTeco($userTodayCheckInDetail, $user, $status, $attendanceDate, $timePart, $coordinate);
        } else {
            $this->processNewAttendanceZKTeco($user, $status, $attendanceDate, $timePart, $coordinate);
        }
    }

// Handles single attendance for ZKTeco (adapted from mobile)

    /**
     * @throws \Exception
     */
    private function handleSingleAttendanceZKTeco($user, $status, $attendanceDate, $timePart, $coordinate)
    {
        $select = ['id', 'user_id', 'check_out_at', 'check_in_at'];
        $userTodayCheckInDetail = Attendance::select($select)
                ->where('user_id', $user->id)
                ->where('attendance_date', $attendanceDate)
                ->orderBy('created_at','desc')
                ->first();

        if ($userTodayCheckInDetail) {
            $this->processSingleExistingAttendanceZKTeco($userTodayCheckInDetail, $user, $status, $attendanceDate, $timePart, $coordinate);
        } else {
            $this->processNewAttendanceZKTeco($user, $status, $attendanceDate, $timePart, $coordinate);
        }
    }



// Adapted processExistingAttendance

    /**
     * @throws \Exception
     */
    private function processExistingAttendanceZKTeco($userTodayCheckInDetail, $user, $status, $attendanceDate, $timePart, $coordinate)
    {
        if ($userTodayCheckInDetail->check_out_at) {
            $this->processNewCheckInZKTeco($user, $attendanceDate, $timePart, $coordinate,$status);
        } else {
            if ($status == 1) { // Only process check-out if status is 1
                $this->processCheckOutZKTeco($userTodayCheckInDetail, $user, $timePart, $coordinate);
            } else {
                Log::warning("Invalid status for existing attendance: {$status} for user {$user->employee_code}");
            }
        }
    }

// Adapted processSingleExistingAttendance

    /**
     * @throws \Exception
     */
    private function processSingleExistingAttendanceZKTeco($userTodayCheckInDetail, $user, $status, $attendanceDate, $timePart, $coordinate)
    {
        if ($userTodayCheckInDetail->check_in_at && $status == 0) { // Already checked in, can't check in again
            Log::warning("Alert: Already checked in for single attendance user {$user->employee_code}");
            return;
        }

        if ($userTodayCheckInDetail->check_out_at) {
            Log::warning("Alert: Already checked out for single attendance user {$user->employee_code}");
            return;
        }

        if ($status == 1) { // Process check-out
            $this->processCheckOutZKTeco($userTodayCheckInDetail, $user, $timePart, $coordinate);
        } else {
            Log::warning("Invalid status for single existing attendance: {$status} for user {$user->employee_code}");
        }
    }

// Adapted processNewAttendance
    private function processNewAttendanceZKTeco($user, $status, $attendanceDate, $timePart, $coordinate)
    {
        if ($status == 1) { // Can't check out without check-in
            Log::warning("Not checked in yet for user {$user->employee_code}");
            return;
        }

        $this->processNewCheckInZKTeco($user, $attendanceDate, $timePart, $coordinate,$status);
    }

// Adapted processNewCheckIn
    private function processNewCheckInZKTeco($user, $attendanceDate, $timePart, $coordinate,$status)
    {
        if($status == 0){
            Attendance::create([
                'company_id' => $user->company_id,
                'attendance_date' => $attendanceDate,
                'office_time_id' => $user->office_time_id,
                'user_id' => $user->id,
                'check_in_at' => $timePart,
                'check_in_type' => EmployeeAttendanceTypeEnum::biometric->value,
                'check_in_latitude' => $coordinate['latitude'],
                'check_in_longitude' => $coordinate['longitude'],
            ]);
            Log::info("New check-in successful for user {$user->employee_code}");

        }

    }

// Adapted processCheckOut

    /**
     * @throws \Exception
     */
    private function processCheckOutZKTeco($userTodayCheckInDetail, $user, $timePart, $coordinate)
    {
        $checkOutAt = $timePart;
        $workedData = AttendanceHelper::calculateWorkedHour($checkOutAt, $userTodayCheckInDetail->check_in_at, $user->id);

        $userTodayCheckInDetail->update([
            'check_out_at' => $checkOutAt,
            'check_out_type' => EmployeeAttendanceTypeEnum::biometric->value,
            'worked_hour' => $workedData['workedHours'],
            'overtime' => $workedData['overtime'],
            'undertime' => $workedData['undertime'],
            'check_out_latitude' => $coordinate['latitude'],
            'check_out_longitude' => $coordinate['longitude'],
        ]);
        Log::info("Check-out successful for user {$user->employee_code}");
    }


    /**
     * @throws \Exception
     */
    private function handleSingleNightAttendanceZKTeco($user, $status, $attendanceDate, $timePart, $coordinate)
    {
        $attendanceStatus = AttendanceHelper::checkNightShiftCheckOut($user->id);

        if ($status == 0 && $attendanceStatus === 'checkin') { // Check-in
            // Check for existing open record to avoid duplicates
            $existingOpen = Attendance::where('user_id', $user->id)
                ->whereNotNull('night_checkin')
                ->whereNull('night_checkout')
                ->exists();
            if ($existingOpen) {
                Log::warning("Alert: Already checked in for night shift user {$user->employee_code}");
                return;
            }
            $this->processNewNightAttendanceZKTeco($user, $attendanceDate, $timePart, $coordinate);
        } elseif ($status == 1 && $attendanceStatus === 'checkout') { // Check-out
            $select = ['id', 'user_id', 'night_checkin', 'night_checkout'];
            $userTodayCheckInDetail = Attendance::select($select)
                ->where('user_id', $user->id)
                ->whereNotNull('night_checkin')
                ->whereNull('night_checkout')
                ->orderBy('night_checkin', 'desc')  // Order by night_checkin to get the most recent open shift
                ->first();

            if (!$userTodayCheckInDetail) {
                Log::warning("No open night shift attendance found for checkout for user {$user->employee_code}");
                return;
            }

            $this->processSingleExistingNightAttendanceZKTeco($userTodayCheckInDetail, $user, $attendanceDate, $timePart, $coordinate);
        } elseif ($attendanceStatus === 'checkout_error') {
            Log::warning("Early checkout for night shift user {$user->employee_code}");
        } else {
            Log::warning("Invalid attendance action for night shift user {$user->employee_code}, status {$status}");
        }
    }
// Adapted processSingleExistingNightAttendance

    /**
     * @throws \Exception
     */
    private function processSingleExistingNightAttendanceZKTeco($userTodayCheckInDetail, $user, $attendanceDate, $timePart, $coordinate)
    {
        if ($userTodayCheckInDetail->night_checkout) {
            Log::warning("Alert: Already checked out for night shift user {$user->employee_code}");
            return;
        }

        $this->processNightCheckOutZKTeco($userTodayCheckInDetail, $user, $timePart, $coordinate, $attendanceDate);
    }

    // Adapted processNewNightAttendanceZKTeco with full datetime
    private function processNewNightAttendanceZKTeco($user, $attendanceDate, $timePart, $coordinate)
    {
        $fullTime = $attendanceDate . ' ' . $timePart;

        Attendance::create([
            'company_id' => $user->company_id,
            'attendance_date' => $attendanceDate,
            'office_time_id' => $user->office_time_id,
            'user_id' => $user->id,
            'night_checkin' => $fullTime,  // Full datetime since field is datetime type
            'check_in_type' => EmployeeAttendanceTypeEnum::biometric->value,
            'check_in_latitude' => $coordinate['latitude'],
            'check_in_longitude' => $coordinate['longitude'],
        ]);
        Log::info("New night check-in successful for user {$user->employee_code}");
    }

    // Adapted processNightCheckOutZKTeco with full datetime and adjusted calculation

    /**
     * @throws \Exception
     */
    private function processNightCheckOutZKTeco($userTodayCheckInDetail, $user, $timePart, $coordinate, $attendanceDate)  // Added $attendanceDate parameter for full time
    {
        $fullTime = $attendanceDate . ' ' . $timePart;

        $workedData = AttendanceHelper::calculateWorkedHour($fullTime, $userTodayCheckInDetail->night_checkin, $user->id);  // Assuming helper can handle full datetimes for cross-day calculation

        $userTodayCheckInDetail->update([
            'night_checkout' => $fullTime,  // Full datetime since field is datetime type
            'check_out_type' => EmployeeAttendanceTypeEnum::biometric->value,
            'check_out_latitude' => $coordinate['latitude'],
            'check_out_longitude' => $coordinate['longitude'],
            'worked_hour' => $workedData['workedHours'],
            'overtime' => $workedData['overtime'],
            'undertime' => $workedData['undertime'],
        ]);
        Log::info("Night check-out successful for user {$user->employee_code}");
    }

}
