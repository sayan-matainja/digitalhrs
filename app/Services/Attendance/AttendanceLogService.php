<?php

namespace App\Services\Attendance;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\DateConverter;
use App\Models\Attendance;
use App\Models\User;
use App\Repositories\AppSettingRepository;
use App\Repositories\AttendanceLogRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\BranchRepository;
use App\Repositories\LeaveRepository;
use App\Repositories\RouterRepository;
use App\Repositories\TimeLeaveRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceLogService
{

    public function __construct(protected AttendanceLogRepository $attendanceLogRepository
    )
    {}

    /**
     * @throws Exception
     */
    public function getAttendanceLog($filterData)
    {
        return $this->attendanceLogRepository->getAll($filterData);
    }

    public function getBiometricAttendanceLog($filterData)
    {
        return $this->attendanceLogRepository->getAllBiometricLogs($filterData);
    }

    public function findLogsByEmployeeId($employeeId)
    {
        return $this->attendanceLogRepository->findByEmployeeId($employeeId);
    }


    /**
     * @throws Exception
     */
    public function findAttendanceLogById($id,$select=['*'])
    {
        return $this->attendanceLogRepository->find($id,$select);
    }
    /**
     * @throws Exception
     */
    public function updateAttendanceLog($id,$validatedData)
    {

        $attendanceLogDetail = $this->findAttendanceLogById($id);
        return $this->attendanceLogRepository->updateAttendanceLog($attendanceLogDetail, $validatedData);

    }

    public function delete($id)
    {

        $attendanceDetail = $this->findAttendanceLogById($id);

        $this->attendanceLogRepository->delete($attendanceDetail);

    }

    public function createAttendanceLog($validatedData)
    {


        return $this->attendanceLogRepository->store($validatedData);

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


}
