<?php

namespace App\Http\Controllers\Api;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Requests\Attendance\AttendanceCheckInRequest;
use App\Requests\Attendance\AttendanceCheckOutRequest;
use App\Resources\Attendance\EmployeeAttendanceDetailCollection;
use App\Resources\Attendance\NightAttendanceResource;
use App\Resources\Attendance\TodayAttendanceResource;
use App\Resources\Dashboard\EmployeeAttendanceToday;
use App\Resources\Dashboard\EmployeeTodayAttendance;
use App\Services\Attendance\AttendanceService;
use App\Services\Attendance\AttendanceLogService;
use App\Services\Nfc\NfcService;
use App\Services\Qr\QrCodeService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class AttendanceApiController extends Controller
{
    use CustomAuthorizesRequests;
    private string $displayMessage = '';
    private array $data = [];
    private array $notificationData = [];
    public function __construct(protected AttendanceService $attendanceService,
    protected QrCodeService $qrCodeService,
    protected NfcService $nfcService,
    protected UserRepository $userRepository,
    protected AttendanceLogService $attendanceLogService)
    {}

    public function getEmployeeAllAttendanceDetailOfTheMonth(Request $request): JsonResponse
    {
        try{
            $isBsEnabled = AppHelper::ifDateInBsEnabled();

            $filterParameter['month'] = $request->month ?? null;
            $filterParameter['user_id'] = getAuthUserCode();
            $with = ['employeeTodayAttendance:user_id,check_in_at,check_out_at,attendance_date,worked_hour,night_checkin,night_checkout,overtime,undertime'];
            $select = [
                'users.id',
                'users.name',
                'users.email'
            ];
            $attendanceDetail = $this->attendanceService->getEmployeeAttendanceDetailOfTheMonthFromUserRepo($filterParameter, $select, $with);

            if ($isBsEnabled) {
                $yearMonth = AppHelper::getCurrentNepaliYearMonth();
                $year = $yearMonth['year'];
                $month = $filterParameter['month'] ?? $yearMonth['month'];
            } else {
                $year = date('Y');
                $month = $filterParameter['month'] ?? date('m');
            }

            $attendanceSummary = AttendanceHelper::getMonthlyDetail($filterParameter['user_id'], $isBsEnabled, $year, $month);


            $returnData['user_detail'] = $attendanceDetail['userDetail'];

            if ($attendanceDetail['todayAttendance']) {

                $returnData['employee_today_attendance'] =  new EmployeeAttendanceToday($attendanceDetail['todayAttendance']);

            } else {
                $returnData['employee_today_attendance'] = [
                    'check_in_at' => '-',
                    'check_out_at' => '-',
                    'productive_time' => 0
                ];
            }

            $returnData['employee_attendance'] = $attendanceDetail['employeeMonthlyAttendance'];

            $returnData['attendance_summary'] = [
                'totalDays' => $attendanceSummary['totalDays'],
                'totalWeekend' => $attendanceSummary['totalWeekend'],
                'totalPresent' => $attendanceSummary['totalPresent'],
                'totalHoliday' => $attendanceSummary['totalHoliday'] + $attendanceSummary['totalWeekend'],
                'totalAbsent' => $attendanceSummary['totalAbsent'],
                'totalTimeLeave' => $attendanceSummary['totalTimeLeave'],
                'totalLeave' => $attendanceSummary['totalLeave'],
                'halfLeaveDays' => $attendanceSummary['halfLeaveDays'],
                'totalWorkedHours' => $attendanceSummary['totalWorkedHours'],
                'totalWorkingHours' => $attendanceSummary['totalWorkingHours'],
            ];

            return AppHelper::sendSuccessResponse(__('index.data_found'), $returnData);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

 /**
     * @throws Exception
     */
    public function employeeAttendance(Request $request): JsonResponse
    {
        $this->authorize('allow_checkin_checkout');
        try {

            $validator = Validator::make($request->all(), [
                'attendance_type' => [new Enum(EmployeeAttendanceTypeEnum::class)],
                'latitude' => ['nullable'],
                'longitude' => ['nullable'],
                'router_bssid' => ['nullable'],
                'identifier' => ['nullable', 'required_if:attendance_type,' . EmployeeAttendanceTypeEnum::qr->value, 'required_if:attendance_type,' . EmployeeAttendanceTypeEnum::nfc->value,],
                'attendance_status_type' => ['nullable', 'required_if:attendance_type,' . EmployeeAttendanceTypeEnum::wifi->value],
                'note'=>['nullable'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('index.validation_failed'),
                    'errors' => $validator->errors()->toArray()
                ],422);
            }

            $validatedData = $validator->validated();


            $validatedData['attendance_status_type'] = $validatedData['attendance_status_type'] ?? '';
            $userDetail = auth()->user();

            $validatedData['user_id'] = $userDetail['id'];
            $validatedData['company_id'] = $userDetail['company_id'];
            $validatedData['office_time_id'] = $userDetail['office_time_id'];
            $validatedData['allow_holiday_check_in'] = $userDetail['allow_holiday_check_in'];

            $this->storeAttendanceLog($validatedData, $userDetail);

            DB::beginTransaction();

            if ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::nfc->value)
            {
                $nfcData = $this->nfcService->verifyNfc($validatedData['identifier']);

                if (!$nfcData) {
                    throw new Exception(__('index.invalid_nfc'), 400);
                }
            } elseif ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::qr->value)
            {
                $attendanceQr = $this->qrCodeService->verifyQr($validatedData['identifier']);

                if (!$attendanceQr) {

                    throw new Exception(__('index.invalid_qr'), 400);
                }

            } elseif ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value)
            {
                $coordinate = $this->attendanceService->newAuthorizeAttendance($validatedData['router_bssid'], $validatedData['user_id']);


                $isCheckIn = $validatedData['attendance_status_type'] === 'checkIn';
                $latitudeKey = $isCheckIn ? 'check_in_latitude' : 'check_out_latitude';
                $longitudeKey = $isCheckIn ? 'check_in_longitude' : 'check_out_longitude';

                $validatedData[$latitudeKey] = ($userDetail['workspace_type'] == User::OFFICE) ? ($coordinate['latitude'] ?? $validatedData['latitude']): $validatedData['latitude'];
                $validatedData[$longitudeKey] = ($userDetail['workspace_type'] == User::OFFICE)? ($coordinate['longitude'] ?? $validatedData['longitude']): $validatedData['longitude'];

            } else {
                return response()->json(['success' => false, 'message' => __('index.invalid_attendance_type')]);
            }

            $multipleAttendance = AppHelper::getAttendanceLimit();
            $nightShift = AppHelper::isOnNightShift($validatedData['user_id']);
            $validatedData['night_shift'] = $nightShift;

            if ($nightShift) {
                    $this->handleSingleNightAttendance($validatedData);
            } else {
                if ($multipleAttendance > 1) {

                    $this->handleMultipleAttendance($validatedData, $multipleAttendance);
                } else {
                    $this->handleSingleAttendance($validatedData);
                }
            }

            DB::commit();

            $this->sendNotification($this->notificationData['title'],$this->notificationData['permissionKey'],$this->notificationData['time'],$this->notificationData['workedTime'] ?? null  );
            return AppHelper::sendSuccessResponse($this->displayMessage, $this->data);


        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Deprecated Don't use this now
    */
    public function employeeCheckIn(AttendanceCheckInRequest $request): JsonResponse
    {
        try {
            $this->authorize('check_in');
            $permissionKeyForNotification = 'employee_check_in';
            $userDetail = auth()->user();

            $validatedData = $request->validated();

            $validatedData['user_id'] = $userDetail->id;
            $validatedData['company_id'] = $userDetail->company_id;


            $this->attendanceService->authorizeAttendance($validatedData['router_bssid'], $validatedData['user_id']);

            $checkIn = $this->attendanceService->employeeCheckIn($validatedData);
            $data = new TodayAttendanceResource($checkIn);

            AppHelper::sendNotificationToAuthorizedUser(
                __('index.check_in_notification'),
                __('index.employee_checked_in', [
                    'name' => ucfirst(auth()->user()->name),
                    'time' => AttendanceHelper::changeTimeFormatForAttendanceView($checkIn->check_in_at)]),

                $permissionKeyForNotification
            );
            return AppHelper::sendSuccessResponse(__('index.check_in_successful'), $data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }
    /**
     * @Deprecated Don't use this now
     */
    public function employeeCheckOut(AttendanceCheckOutRequest $request): JsonResponse
    {
        try {
            $userDetail = auth()->user();

            $this->authorize('check_out');
            $permissionKeyForNotification = 'employee_check_out';

            $validatedData = $request->validated();
            $validatedData['user_id'] = $userDetail->id;
            $validatedData['company_id'] = $userDetail->company_id;

            $checkOut = $this->attendanceService->employeeCheckOut($validatedData);
            $data = new TodayAttendanceResource($checkOut);
            $workedTime = AttendanceHelper::getEmployeeWorkedTimeInHourAndMinute($checkOut);

            AppHelper::sendNotificationToAuthorizedUser(
                __('index.check_out_notification'),
                __('index.employee_checked_out_and_worked', [
                        'name' => ucfirst(auth()->user()->name),
                        'check_out_time' => AttendanceHelper::changeTimeFormatForAttendanceView($checkOut->check_out_at),
                        'worked_time' => $workedTime]),
                $permissionKeyForNotification

            );
            return AppHelper::sendSuccessResponse(__('index.check_out_successful'), $data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }


    /**
     * @throws Exception
     */
    private function handleMultipleAttendance($validatedData, $multipleAttendance)
    {
        $select = ['id', 'user_id', 'check_out_at', 'check_in_at'];
        $userTodayCheckInDetail = $this->attendanceService->findEmployeeTodayAttendanceDetail($validatedData['user_id'], $select);
        $attendanceDataCount = $this->attendanceService->findEmployeeTodayAttendanceNumbers($validatedData['user_id']);

        if (isset($userTodayCheckInDetail->check_out_at) && ($multipleAttendance == $attendanceDataCount)) {
            throw new Exception(__('index.multi_checkout_warning'), 400);
        }

        if ($userTodayCheckInDetail) {
            $this->processExistingAttendance($userTodayCheckInDetail, $validatedData);
        } else {
            $this->processNewAttendance($validatedData);
        }
    }

    private function handleSingleAttendance($validatedData)
    {
        $select = ['id', 'user_id', 'check_out_at', 'check_in_at'];
        $userTodayCheckInDetail = $this->attendanceService->findEmployeeTodayAttendanceDetail($validatedData['user_id'], $select);

        if ($userTodayCheckInDetail) {
            $this->processSingleExistingAttendance($userTodayCheckInDetail, $validatedData);
        } else {
            $this->processNewAttendance($validatedData);
        }
    }

    /**
     * @throws Exception
     */
    private function processExistingAttendance($userTodayCheckInDetail, $validatedData)
    {
        $attendanceTypes = [EmployeeAttendanceTypeEnum::qr->value,EmployeeAttendanceTypeEnum::nfc->value];
        if ($userTodayCheckInDetail->check_out_at) {
            if ( (in_array($validatedData['attendance_type'], $attendanceTypes)) || (($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value) && ($validatedData['attendance_status_type'] == 'checkIn'))) {
                $this->processNewCheckIn($validatedData);
            } elseif ( ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value) && ($validatedData['attendance_status_type'] == 'checkOut')) {
                throw new Exception('already checked out', 400);
            }else{
                throw new Exception('already checked in', 400);
            }

        } else {
            if ( (in_array($validatedData['attendance_type'], $attendanceTypes)) || (($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value) && ($validatedData['attendance_status_type'] == 'checkOut'))) {
                $this->processCheckOut($userTodayCheckInDetail, $validatedData);
            }elseif ( (($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value) && ($validatedData['attendance_status_type'] == 'checkIn'))) {
                throw new Exception('already checked in', 400);
            }else{
                throw new Exception('already checked out', 400);
            }

        }
    }

    /**
     * @throws Exception
     */
    private function processSingleExistingAttendance($userTodayCheckInDetail, $validatedData)
    {
        if ($userTodayCheckInDetail->check_in_at && $validatedData['attendance_status_type'] == 'checkIn') {
            throw new Exception(__('index.alert_checkin'), 400);
        }

        if ($userTodayCheckInDetail->check_out_at) {
            throw new Exception(__('index.checkout_alert'), 400);
        }

        $this->processCheckOut($userTodayCheckInDetail, $validatedData);
    }

    /**
     * @throws Exception
     */
    private function processNewAttendance($validatedData)
    {
        if ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value && $validatedData['attendance_status_type'] == 'checkOut') {
            throw new Exception(__('index.not_checked_in_yet'), 400);
        }

        $this->processNewCheckIn($validatedData);
    }

    /**
     * @throws Exception
     */
    private function processNewCheckIn($validatedData)
    {
        $validatedData['check_in_type'] = $validatedData['attendance_type'];
        $validatedData['check_in_note'] = $validatedData['note'] ?? '';
        $attendanceData = $this->attendanceService->newCheckIn($validatedData);

        $this->notificationData['title'] = __('index.check_in_notification');
        $this->notificationData['permissionKey'] = 'employee_check_in';
        $this->notificationData['time'] = $attendanceData->check_in_at;

        $this->data = (new TodayAttendanceResource($attendanceData))->toArray(request());
        $this->displayMessage = __('index.check_in_successful');
    }

    /**
     * @throws Exception
     */
    private function processCheckOut($userTodayCheckInDetail, $validatedData)
    {
        $validatedData['check_out_type'] = $validatedData['attendance_type'];
        $validatedData['check_out_note'] = $validatedData['note'] ?? '';

        $attendanceData = $this->attendanceService->newCheckOut($userTodayCheckInDetail, $validatedData);

        $workedTime = AttendanceHelper::getEmployeeWorkedTimeInHourAndMinute($attendanceData);

        $this->notificationData['title'] = __('index.check_out_notification');
        $this->notificationData['permissionKey'] = 'employee_check_out';
        $this->notificationData['time'] = $attendanceData->check_out_at;
        $this->notificationData['workedTime'] = $workedTime;

        $this->data =(new TodayAttendanceResource($attendanceData))->toArray(request());
        $this->displayMessage = __('index.check_out_successful');
    }

    private function sendNotification($title, $permissionKey, $time, $workedTime = null)
    {
        $timeFormat = AttendanceHelper::changeTimeFormatForAttendanceView($time);

        if ($permissionKey == 'employee_check_in') {
            $message = __('index.employee_checked_in', ['name' => ucfirst(auth()->user()->name), 'time' => $timeFormat]);
        } else {
            $message = __('index.employee_checked_out', ['name' => ucfirst(auth()->user()->name), 'time' => $timeFormat]);
        }

        if ($workedTime) {
            $message .= ' ' . __('index.has_worked_for', ['time' => $workedTime]);
        }


        AppHelper::sendNotificationToAuthorizedUser(
            $title,
            $message,
            $permissionKey
        );
    }

    public function storeAttendanceLog($validatedData, $userDetail)
    {
        try{
            DB::beginTransaction();
            $logData = [
                'attendance_type' => $validatedData['attendance_type'],
                'identifier' => ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value) ? $validatedData['router_bssid'] : $validatedData['identifier'],
            ];

            $attendanceLog = $this->attendanceLogService->findLogsByEmployeeId($userDetail['id']);

            if(isset($attendanceLog)){

                $this->attendanceLogService->updateAttendanceLog($attendanceLog->id, $logData);
            }else{
                $logData['employee_id']= $userDetail['id'];

                $this->attendanceLogService->createAttendanceLog($logData);
            }
            DB::commit();
        }catch (Exception $exception){
            DB::rollBack();
        }


    }

    /**
     * @throws Exception
     */
    private function handleSingleNightAttendance($validatedData)
    {
        $attendanceStatus = AttendanceHelper::checkNightShiftCheckOut($validatedData['user_id']);

        // Handle WiFi attendance (attendance_status_type is present)
        if ($validatedData['attendance_status_type'] != '') {
            if ($validatedData['attendance_status_type'] === 'checkIn' && $attendanceStatus === 'checkin') {
                $this->processNewNightAttendance($validatedData);
            } elseif ($validatedData['attendance_status_type'] === 'checkOut' && $attendanceStatus === 'checkout') {
                $select = ['id', 'user_id', 'night_checkin', 'night_checkout'];
                $userTodayCheckInDetail = $this->attendanceService->findEmployeeAttendanceDetailForNightShift($validatedData['user_id'], $select);
                $this->processSingleExistingNightAttendance($userTodayCheckInDetail, $validatedData);
            } elseif ($attendanceStatus === 'checkout_error') {
                throw new Exception(__('message.early_checkout'), 400);
            } else {
                throw new Exception(__('index.attendance_alert_status', ['status' => ucfirst($validatedData['attendance_status_type'])]), 400);
            }
        }
        // Handle QR and NFC attendance (attendance_status_type is not present)
        else {
            if ($attendanceStatus === 'checkin') {
                $this->processNewNightAttendance($validatedData);
            } elseif ($attendanceStatus === 'checkout') {
                $select = ['id', 'user_id', 'night_checkin', 'night_checkout'];
                $userTodayCheckInDetail = $this->attendanceService->findEmployeeAttendanceDetailForNightShift($validatedData['user_id'], $select);
                $this->processSingleExistingNightAttendance($userTodayCheckInDetail, $validatedData);
            } elseif ($attendanceStatus === 'checkout_error') {
                throw new Exception(__('message.early_checkout'), 400);
            } else {
                throw new Exception(__('index.invalid_attendance_action'), 400);
            }
        }
    }
    /**
     * @throws Exception
     */
    private function processSingleExistingNightAttendance($userTodayCheckInDetail, $validatedData)
    {
        if ($userTodayCheckInDetail->night_checkin && $validatedData['attendance_status_type'] == 'checkIn') {
            throw new Exception(__('index.alert_multi_checkin_shift'), 400);
        }

        if ($userTodayCheckInDetail->night_checkout) {
            throw new Exception(__('index.checkout_alert_for_shift'), 400);
        }


        $this->processNightCheckOut($userTodayCheckInDetail, $validatedData);
    }

    /**
     * @throws Exception
     */
    private function processNewNightAttendance($validatedData)
    {
        if ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value && $validatedData['attendance_status_type'] == 'checkOut') {
            throw new Exception(__('index.not_checked_in_yet'), 400);
        }

        $this->processNewNightCheckIn($validatedData);
    }

    /**
     * @throws Exception
     */
    private function processNewNightCheckIn($validatedData)
    {
        $validatedData['check_in_type'] = $validatedData['attendance_type'];
        $validatedData['check_in_note'] = $validatedData['note'] ?? '';
        $attendanceData = $this->attendanceService->newCheckIn($validatedData);

        $this->notificationData['title'] = __('index.check_in_notification');
        $this->notificationData['permissionKey'] = 'employee_check_in';
        $this->notificationData['time'] = $attendanceData->night_checkin;

        $this->data = (new NightAttendanceResource($attendanceData))->toArray(request());
        $this->displayMessage = __('index.check_in_successful');
    }

    /**
     * @throws Exception
     */
    private function processNightCheckOut($userTodayCheckInDetail, $validatedData)
    {
        $validatedData['check_out_type'] = $validatedData['attendance_type'];
        $validatedData['check_out_note'] = $validatedData['note'] ?? '';

        $attendanceData = $this->attendanceService->newCheckOut($userTodayCheckInDetail, $validatedData);
        $workedTime = AttendanceHelper::getEmployeeWorkedTimeForNightShift($attendanceData);


        $this->notificationData['title'] = __('index.check_out_notification');
        $this->notificationData['permissionKey'] = 'employee_check_out';
        $this->notificationData['time'] = $attendanceData->night_checkout;
        $this->notificationData['workedTime'] = $workedTime;
        $this->data = (new NightAttendanceResource($attendanceData))->toArray(request());
        $this->displayMessage = __('index.check_out_successful');
    }

}
