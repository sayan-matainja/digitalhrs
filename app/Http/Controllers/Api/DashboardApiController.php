<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Repositories\FeatureRepository;
use App\Repositories\UserRepository;
use App\Resources\award\RecentAwardResource;
use App\Resources\Dashboard\CompanyWeekendResource;
use App\Resources\Dashboard\EmployeeTodayAttendance;
use App\Resources\Dashboard\EmployeeWeeklyReport;
use App\Resources\Dashboard\FeatureCollection;
use App\Resources\Dashboard\FeatureResource;
use App\Resources\Dashboard\OfficeTimeResource;
use App\Resources\Dashboard\OverviewResource;
use App\Resources\Dashboard\ThemeSettingResource;
use App\Resources\Dashboard\UserReportResource;
use App\Resources\Event\EventResource;
use App\Resources\Holiday\HolidayCollection;
use App\Resources\Loan\LoanInstallmentResource;
use App\Resources\Training\TrainingResource;
use App\Resources\User\CompanyResource;
use App\Resources\User\HolidayResource;
use App\Resources\User\TeamSheetCollection;
use App\Services\AwardManagement\AwardService;
use App\Services\EventManagement\EventService;
use App\Services\Holiday\HolidayService;
use App\Services\LoanManagement\LoanRepaymentService;
use App\Services\LoanManagement\LoanService;
use App\Services\ThemeSetting\ThemeSettingService;
use App\Services\TrainingManagement\TrainingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DashboardApiController extends Controller
{
    public function __construct(protected UserRepository $userRepo, protected HolidayService $holidayService, protected FeatureRepository $featureRepository,
                                protected AwardService $awardService, protected EventService $eventService, protected TrainingService $trainingService,
                                protected ThemeSettingService $themeSettingService, protected LoanRepaymentService $repaymentService)
    {}

    public function userDashboardDetail(Request $request): JsonResponse
    {
        try {
            $fcmToken =  $request->header('fcm_token');
            $nfc_key = 'create_nfc';
            $userId = getAuthUserCode();
            $with = [
                'branch:id,name',
                'company:id,name,weekend',
                'post:id,post_name',
                'department:id,dept_name',
                'role:id,name',
                'officeTime',
                'employeeTodayAttendance:user_id,check_in_at,check_out_at,attendance_date,night_checkin,night_checkout',
                'employeeWeeklyAttendance:user_id,check_in_at,check_out_at,attendance_date,night_checkin,night_checkout'
            ];
            $dashboard = [];
            $select = ['users.*', 'branch_id', 'company_id', 'department_id', 'post_id', 'role_id'];
            $date = AppHelper::yearDetailToFilterData();

            $userDetail = $this->userRepo->findUserDetailById($userId, $select, $with);

            if(isset($fcmToken) && !empty($fcmToken)){
                $this->userRepo->updateUserFcmToken($userDetail,$fcmToken);
            }

            $teamMembers = $this->userRepo->getAllActiveEmployeeOfDepartment($userDetail->department_id,['*'],['branch:id,name', 'post:id,post_name', 'department:id,dept_name']);

            $holiday = $this->holidayService->getCurrentActiveHoliday();


            $overview = $this->userRepo->getEmployeeOverviewDetail($userId,$date);

            $trainingOverView = $this->trainingService->getSummary($userId);
            $overview->upcoming_training = $trainingOverView->upcoming_training;
            $shiftDates = $this->getAllDatesForShiftNotification($userDetail);
            $features = $this->featureRepository->getAllFeatures();


            $isAwardFeatured = $features->where('key', 'award')->where('status', 1)->first() !== null;
            $isTrainingFeatured = $features->where('key', 'training')->where('status', 1)->first() !== null;
            $isEventFeatured = $features->where('key', 'event')->where('status', 1)->first() !== null;


            $recentAward = $isAwardFeatured ? $this->awardService->getRecentEmployeeAward(['*'],['employee:id,name,avatar', 'type:id,title']) : null;
            $recentEvent = $isEventFeatured ? $this->eventService->getRecentEvents() : null;
            $withTraining = ['trainingType:id,title','employeeTraining.employee:id,name','branch:id,name','trainingDepartment.department:id,dept_name','trainingDepartment.department:id,dept_name'];
            $recentTraining = $isTrainingFeatured ? $this->trainingService->getRecentEmployeeTraining(['*'],$withTraining,$userId) : null;

            $isLoanFeatured = $features->where('key', 'loan')->where('status', 1)->first() !== null;
            $recentLoan = $isLoanFeatured ? $this->repaymentService->getRecentLoanInstallment() : null;
            $themeSetting = $this->themeSettingService->getAllThemes();

            $dashboard['user'] = new UserReportResource($userDetail);
            $dashboard['employee_today_attendance'] = new EmployeeTodayAttendance($userDetail);
            $dashboard['overview'] = new OverviewResource($overview);
            $dashboard['office_time'] = new OfficeTimeResource($userDetail);
            $dashboard['company'] = new CompanyWeekendResource($userDetail);
            $dashboard['employee_weekly_report'] = new EmployeeWeeklyReport($userDetail);

            $dashboard['date_in_ad'] = !AppHelper::ifDateInBsEnabled();
            $dashboard['attendance_note'] = AppHelper::ifAttendanceNoteEnabled();
            $dashboard['attendance_method'] = array_values(array_diff(AppHelper::attendanceMethod(),['biometric']));
            $dashboard['employee_location'] = AppHelper::isEmployeeLocationRequired();
            $dashboard['shift_dates'] = $shiftDates;
            $dashboard['features'] = new FeatureCollection($features);
            $dashboard['teamMembers'] = new TeamSheetCollection($teamMembers);
            $dashboard['add_nfc'] = AppHelper::checkRoleIdWithGivenPermission($userDetail->role_id, $nfc_key);
            $dashboard['theme'] = new ThemeSettingResource($themeSetting);

            if (isset($holiday)) {
                $dashboard['recent_holiday'] = new HolidayResource($holiday);
            } else {
                $dashboard['recent_holiday'] = null;
            }
            if (isset($recentAward)) {
                $date = Carbon::createFromFormat('Y-m-d', $recentAward->awarded_date);
                $daysAdd = AppHelper::getAwardDisplayLimit();
                $endDate = $date->copy()->addDays($daysAdd);

                if (strtotime(date('Y-m-d')) >= strtotime($date->format('Y-m-d')) &&
                    strtotime(date('Y-m-d')) <= strtotime($endDate->format('Y-m-d'))) {
                    $dashboard['recent_award'] = new RecentAwardResource($recentAward);
                } else {
                    $dashboard['recent_award'] = null;
                }
            } else {
                $dashboard['recent_award'] = null;
            }
            if (isset($recentTraining)) {

                $dashboard['recent_training'] =  new TrainingResource($recentTraining);

            } else {
                $dashboard['recent_training'] = null;
            }
            if (isset($recentEvent)) {

                $dashboard['recent_event'] =  new EventResource($recentEvent);

            } else {
                $dashboard['recent_event'] = null;
            }


            if (isset($recentLoan)) {

                $dashboard['loan_installment'] =  new LoanInstallmentResource($recentLoan);

            } else {
                $dashboard['loan_installment'] = null;
            }

            return AppHelper::sendSuccessResponse(__('index.data_found'), $dashboard);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

    /**
     * @throws Exception
     */
    private function getAllDatesForShiftNotification($userDetail)
    {
        $dates = [];
        $numberOfDays = AppHelper::getDaysToFindDatesForShiftNotification();
        $holidaysDetail = $this->holidayService->getAllActiveHolidaysFromNowToGivenNumberOfDays($numberOfDays);
        $weekendWeekDays = $userDetail->company->weekend;
        $nowDate = Carbon::now();
        $endDate = Carbon::now()->addDay($numberOfDays);

        while ($nowDate <= $endDate) {
            $isHoliday = in_array($nowDate->format('Y-m-d'), $holidaysDetail);
            $isWeekend = in_array($nowDate->dayOfWeek, $weekendWeekDays);
            if( !$isHoliday && !$isWeekend){
              $dates[] = $nowDate->format('Y-m-d');
            }
            $nowDate->addDay();
        }
        return $dates;
    }


    public function appSetting(): JsonResponse
    {
        try {

            $userId = getAuthUserCode();

            $nfc_key = 'create_nfc';
            $userDetail = $this->userRepo->findUserDetailById($userId);
            $features = $this->featureRepository->getAllFeatures();
            $themeSetting = $this->themeSettingService->getAllThemes();

            $setting['date_in_ad'] = !AppHelper::ifDateInBsEnabled();
            $setting['attendance_note'] = AppHelper::ifAttendanceNoteEnabled();
            $setting['employee_location'] = AppHelper::isEmployeeLocationRequired();
            $setting['add_nfc'] = AppHelper::checkRoleIdWithGivenPermission($userDetail->role_id, $nfc_key);
            $setting['attendance_method'] = array_values(array_diff(AppHelper::attendanceMethod(),['biometric']));
            $setting['theme'] = new ThemeSettingResource($themeSetting);
            $setting['features'] = new FeatureCollection($features);

            return AppHelper::sendSuccessResponse(__('index.data_found'), $setting);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

}



