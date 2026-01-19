<?php

namespace App\Http\Controllers\Api;

use App\Enum\LeaveStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequestMaster;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Requests\Leave\LeaveRequestStoreRequest;
use App\Requests\Leave\TimeLeaveStoreApiRequest;
use App\Requests\Leave\TimeLeaveStoreRequest;
use App\Resources\award\RecentAwardResource;
use App\Resources\Event\EventCollection;
use App\Resources\Event\EventResource;
use App\Resources\Holiday\HolidayCollection;
use App\Resources\Leave\EmployeeLeaveDetailCollection;
use App\Resources\Leave\EmployeeTimeLeaveDetailCollection;
use App\Resources\Leave\LeaveRequestCollection;
use App\Resources\TeamMeeting\TeamMeetingCollection;
use App\Resources\TeamMeeting\TeamMeetingResource;
use App\Resources\Training\TrainingCollection;
use App\Resources\Training\TrainingResource;
use App\Resources\User\BirthdayCollection;
use App\Resources\User\BirthdayResource;
use App\Resources\User\HolidayResource;
use App\Services\EventManagement\EventService;
use App\Services\Holiday\HolidayService;
use App\Services\Leave\LeaveService;
use App\Services\Leave\TimeLeaveService;
use App\Services\TeamMeeting\TeamMeetingService;
use App\Services\TrainingManagement\TrainingService;
use App\Traits\CustomAuthorizesRequests;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Validation\Rule;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;

class LeaveApiController extends Controller
{
    use CustomAuthorizesRequests;

    public function __construct(protected LeaveService $leaveService, protected TimeLeaveService $timeLeaveService,
                                protected HolidayService $holidayService, protected UserRepository $userRepository,
    protected TrainingService $trainingService, protected EventService $eventService, protected TeamMeetingService $meetingService)
    {}

    public function getAllLeaveRequestOfEmployee(Request $request): JsonResponse
    {
        try{

            $filterParameter = [
                'leave_type' => $request->leave_type ?? null,
                'status' => $request->status ?? null,
                'year' => $request->year ?? \Carbon\Carbon::now()->year,
                'month' => $request->month ?? null,
                'early_exit' => $request->early_exit ?? null,
                'user_id' => getAuthUserCode()
            ];

            $getAllLeaveRequests =  $this->leaveService->getAllLeaveRequestOfEmployee($filterParameter);
            $timeLeaveRequests = $this->timeLeaveService->getAllTimeLeaveRequestOfEmployee($filterParameter);

            if( isset($request) && ($request->leave_type == '' || $request->leave_type == 0)){
                $getAllLeaveRequests = collect($getAllLeaveRequests);
                $timeLeaveRequests = collect($timeLeaveRequests);
                $mergedCollection = $getAllLeaveRequests->merge($timeLeaveRequests);
            }else{
                $mergedCollection = $getAllLeaveRequests;
            }
            $mergedCollection = $mergedCollection->sortByDesc('leave_requested_date');

            $leaveData = new LeaveRequestCollection($mergedCollection);

            return AppHelper::sendSuccessResponse(__('index.data_found'),$leaveData);
        } catch (\Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws AuthorizationException
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function saveLeaveRequestDetail(LeaveRequestStoreRequest $request): JsonResponse
    {
        $this->authorize('leave_request_create');

        try {
            $permissionKeyForNotification = 'employee_leave_request';

            $validatedData = $request->validated();

            $validatedData['requested_by'] = getAuthUserCode();
            $validatedData['branch_id'] = auth()->user()?->branch_id;
            $validatedData['department_id'] = auth()->user()?->department_id;
            DB::beginTransaction();
              $leaveRequestDetail = $this->leaveService->storeLeaveRequest($validatedData);
            DB::commit();

            if($leaveRequestDetail) {

                $notificationTitle = __('index.leave_request_notification');
                if($leaveRequestDetail['leave_for'] == 'half_day'){
                    $notificationMessage = __('message.half_leave_notification_message', [
                        'name' => ucfirst(auth()->user()->name),
                        'from_date' => AppHelper::formatDateForView($leaveRequestDetail['from_date']),
                        'half' => str_replace('_','',$leaveRequestDetail['leave_in']),
                        'reasons' => $validatedData['reasons']
                    ]);
                }else{
                    $notificationMessage = __('index.leave_request_submit', [
                        'name' => ucfirst(auth()->user()->name),
                        'no_of_days' => $leaveRequestDetail['no_of_days'],
                        'leave_from' => AppHelper::formatDateForView($leaveRequestDetail['leave_from']),
                        'leave_requested_date' => AppHelper::convertLeaveDateFormat($leaveRequestDetail['leave_requested_date']),
                        'reasons' => $validatedData['reasons']
                    ]);
                }

                AppHelper::sendNotificationToAuthorizedUser(
                    $notificationTitle,
                    $notificationMessage,
                    $permissionKeyForNotification
                );
            }
            return AppHelper::sendSuccessResponse(__('index.leave_request_submitted_successfully'));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getLeaveCountDetailOfEmployeeOfTwoMonth(): JsonResponse
    {
        try {

            $dateWithNumberOfEmployeeOnLeave = $this->leaveService->getLeaveCountDetailOfEmployeeOfTwoMonth();
            $timeLeaveCount = $this->timeLeaveService->getTimeLeaveCountDetailOfEmployeeOfTwoMonth();
            $leaveCalendar = array_merge($dateWithNumberOfEmployeeOnLeave, $timeLeaveCount);
            return AppHelper::sendSuccessResponse(__('index.data_found'),$leaveCalendar);
        } catch (\Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Deprecated Don't use this now
     */
    public function getAllEmployeeLeaveDetailBySpecificDay(Request $request): JsonResponse
    {
        try {
            $filterParameter['leave_date'] = $request->leave_date ?? Carbon::now()->format('Y-m-d') ;

            $leaveListDetail = $this->leaveService->getAllEmployeeLeaveDetailBySpecificDay($filterParameter);
            $timeLeaveListDetail = $this->timeLeaveService->getAllEmployeeTimeLeaveDetailBySpecificDay($filterParameter);
            $timeLeaveDetail = new EmployeeTimeLeaveDetailCollection($timeLeaveListDetail);
            $leaveDetail = new EmployeeLeaveDetailCollection($leaveListDetail);
            $leaveData = $timeLeaveDetail->concat($leaveDetail);


            return AppHelper::sendSuccessResponse(__('index.data_found'),$leaveData);
        } catch (\Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getCalendarDetailBySpecificDay(Request $request): JsonResponse
    {
        try {

            $filterParameter['leave_date'] = $request->leave_date ?? Carbon::now()->format('Y-m-d') ;

            $leaveListDetail = $this->leaveService->getAllEmployeeLeaveDetailBySpecificDay($filterParameter);
            $timeLeaveListDetail = $this->timeLeaveService->getAllEmployeeTimeLeaveDetailBySpecificDay($filterParameter);
            $timeLeaveDetail = new EmployeeTimeLeaveDetailCollection($timeLeaveListDetail);
            $leaveDetail = new EmployeeLeaveDetailCollection($leaveListDetail);
            $leaveData = $timeLeaveDetail->concat($leaveDetail);

//            $holidaySelect = ['id','event','event_date','note','is_public_holiday'];
            $holiday = $this->holidayService->getHolidayByDate($filterParameter['leave_date']);

            $withBirthday = ['post'];
            $birthdays =  $this->userRepository->getBirthdayUsers($filterParameter['leave_date'],$withBirthday);

            if (isset($holiday)) {
                $holidayData = new HolidayResource($holiday);

            } else {
                $holidayData = null;

            }

            $birthdayData = new BirthdayCollection($birthdays);
            $data = [
                'leaves'=>$leaveData,
                'holiday'=> $holidayData,
                'birthdays'=>$birthdayData
            ];

            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        } catch (\Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getCalendarDetail(): JsonResponse
    {
        try {

            if(AppHelper::ifDateInBsEnabled()) {

                $currentDate = AppHelper::getCurrentMonthDates();

                $startDate = $currentDate['start_date'];
                $endDate = $currentDate['end_date'];
            }else{
                $startDate = date('Y-m-01');
                $endDate = date('Y-m-t', strtotime('+1 month'));
            }


            $filterParameter['start_date'] = $startDate;
            $filterParameter['end_date'] = $endDate;


            $leaveListDetail = $this->leaveService->getAllEmployeeLeaveDetail($filterParameter);
            $timeLeaveListDetail = $this->timeLeaveService->getAllEmployeeTimeLeaveDetail($filterParameter);
            $timeLeaveDetail = new EmployeeTimeLeaveDetailCollection($timeLeaveListDetail);
            $leaveDetail = new EmployeeLeaveDetailCollection($leaveListDetail);


            $holidays = $this->holidayService->getHolidayByDates($filterParameter);

            $weekends = AppHelper::getMonthWeekendDates($filterParameter);

            $weekendData = collect($weekends)->map(function ($day) {
                return (object)[
                    'id'              => 0,
                    'event'           => 'Off day',
                    'event_date'      => date('Y-m-d', strtotime($day)),
                    'note'            =>  'Office off day - '.date('l',strtotime($day)),
                    'is_public_holiday' => 0,
                ];
            });



            $merged = $weekendData->concat($holidays);


            $merged = $merged->sortBy('event_date');
            $holidayData =  new HolidayCollection($merged);


            $withBirthday = ['post'];

            $birthdays =  $this->userRepository->getBirthdayUsersByMonth($filterParameter,$withBirthday);

            $birthdayData = new BirthdayCollection($birthdays);

            // training
            $withTraining = ['trainingType:id,title','employeeTraining.employee:id,name','branch:id,name','trainingInstructor.trainer.employee.department:id,dept_name','trainingDepartment.department:id,dept_name'];
            $trainingDetail = $this->trainingService->getTrainingByMonth($filterParameter, ['*'], $withTraining);

            $trainings = TrainingResource::collection($trainingDetail);
            // event
            $eventDetail = $this->eventService->getMonthEvents($filterParameter);
            $events =  EventResource::collection($eventDetail);
            // meeting
            $teamMeetingData = $this->meetingService->getAssignedMonthTeamMeetingDetail($filterParameter);
            $teamMeeting = TeamMeetingResource::collection($teamMeetingData);
            $data = [
                'leaves'=>$leaveDetail,
                'timeLeaves'=>$timeLeaveDetail,
                'holiday'=> $holidayData,
                'birthdays'=>$birthdayData,
                'trainings'=>$trainings,
                'events'=>$events,
                'teamMeeting'=>$teamMeeting,
            ];
            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        } catch (\Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function cancelLeaveRequest(Request $request, $leaveRequestId)
    {
        try {

            $message = __('index.leave_request_cannot_be_cancelled');
            $leaveRequestDetail = $this->leaveService->findEmployeeLeaveRequestById($leaveRequestId);
            $cancellationReason = $request->input('reason');

            if($leaveRequestDetail->status === LeaveStatusEnum::approved->value && (empty($cancellationReason) || is_null($cancellationReason))){
                return AppHelper::sendErrorResponse('Cancellation Reason is required for approved leave',422);
            }

            if($leaveRequestDetail->status == LeaveStatusEnum::pending->value){
                $updateData = [
                    'status' =>  LeaveStatusEnum::cancelled->value,
                    'cancellation_reason' => null,
                ];

                $message = __('index.leave_request_cancelled_successfully');
            }elseif ($leaveRequestDetail->status == LeaveStatusEnum::approved->value){
                $updateData = [
                    'cancel_request' => true,
                    'cancellation_reason' => $cancellationReason,
                ];
                $message = __('index.leave_cancel_request');

            }else{
                throw new Exception($message,403);
            }

            $this->leaveService->cancelLeaveRequest($updateData, $leaveRequestDetail);
            return AppHelper::sendSuccessResponse($message);
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @throws AuthorizationException
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function saveTimeLeaveRequest(TimeLeaveStoreApiRequest $request): JsonResponse
    {
            $this->authorize('leave_request_create');

        try {
            $permissionKeyForNotification = 'employee_leave_request';

            $validatedData = $request->validated();

            $validatedData['requested_by'] = getAuthUserCode();
            $validatedData['branch_id'] = auth()->user()?->branch_id;
            $validatedData['department_id'] = auth()->user()?->department_id;

            DB::beginTransaction();
            $leaveRequestDetail = $this->timeLeaveService->storeTimeLeaveRequest($validatedData);
            DB::commit();

            if ($leaveRequestDetail) {
                $notificationTitle = __('index.leave_request_notification');
                $notificationMessage = __('index.leave_request_submitted', [
                    'name' => ucfirst(auth()->user()?->name),
                    'start_time' => $leaveRequestDetail['start_time'],
                    'end_time' => $leaveRequestDetail['end_time'],
                    'issue_date' => AppHelper::convertLeaveDateFormat($leaveRequestDetail['issue_date']),
                    'reasons' => $validatedData['reasons']
                ]);
                AppHelper::sendNotificationToAuthorizedUser(
                    $notificationTitle,
                    $notificationMessage,
                    $permissionKeyForNotification
                );
            }
            return AppHelper::sendSuccessResponse(__('index.leave_request_submitted_successfully'));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function cancelTimeLeaveRequest(Request $request, $leaveRequestId)
    {
        try {

            $message = __('index.leave_request_cannot_be_cancelled');
            $leaveRequestDetail = $this->timeLeaveService->findEmployeeTimeLeaveRequestById($leaveRequestId);

            $cancellationReason = $request->input('reason');

            if($leaveRequestDetail->status === LeaveStatusEnum::approved->value && (empty($cancellationReason) || is_null($cancellationReason))){
                return AppHelper::sendErrorResponse('Cancellation Reason is required for approved leave',422);
            }

            if($leaveRequestDetail->status == LeaveStatusEnum::pending->value){
                $updateData = [
                    'status' =>  LeaveStatusEnum::cancelled->value,
                    'cancellation_reason' => null,
                ];

                $message = __('index.leave_request_cancelled');

            }elseif ($leaveRequestDetail->status == LeaveStatusEnum::approved->value){
                $updateData = [
                    'cancel_request' => true,
                    'cancellation_reason' => $cancellationReason,
                ];
                $message = __('index.leave_cancel_request');

            }else{
                throw new Exception($message,403);
            }

            $this->timeLeaveService->cancelLeaveRequest($updateData, $leaveRequestDetail);
            return AppHelper::sendSuccessResponse($message);
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function leaveApprovalHistory($leaveRequestId)
    {
        try {

            $leaveRequestDetail = $this->leaveService->findEmployeeLeaveRequestById($leaveRequestId,['*'],[']']);

            $data = $this->leaveService->getApprovalHistory($leaveRequestDetail);

            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}
