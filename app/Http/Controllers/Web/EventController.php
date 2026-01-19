<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\TeamMeeting;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\Event\EventRequest;
use App\Requests\TeamMeeting\TeamMeetingRequest;
use App\Services\EventManagement\EventService;
use App\Services\Notification\NotificationService;
use App\Traits\CustomAuthorizesRequests;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.event.';

    public function __construct(protected EventService $eventService, protected UserRepository $userRepository,protected NotificationService $notificationService,
                                protected DepartmentRepository $departmentRepository, protected CompanyRepository $companyRepository)
    {}

    public function index(Request $request)
    {
        $this->authorize('list_event');
        try {

            $this->updateEventStatus();
            $isBsEnabled = AppHelper::ifDateInBsEnabled();

            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'training_type_id' => $request->training_type_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'start_date' => $request->start_date ?? null,
                'end_date' => $request->end_date ?? null,
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $events = [];
            $select = ['*'];
            $with = [];
            $perPage = 6;
            $eventLists = $this->eventService->getAllEvents($filterParameters,$select, $with);

            if($isBsEnabled){
                $events = $eventLists;
            }else{
                foreach ($eventLists as $event) {
                    $endDate = $event->end_date
                        ? Carbon::parse($event->end_date)->addDay()->toDateString()
                        : null;
                    $events[] = [
                        'id' => $event->id,
                        'title' => substr($event->title, 0, 12) . (strlen($event->title) > 12 ? '...' : ''),
                        'start' => $event->start_date,
                        'end' => $endDate,
                        'backgroundColor'=>$event->background_color,
                        'borderColor'=>$event->background_color,
                        'allDay' => true,
                    ];
                }
            }

            $upcomingEvents = $this->eventService->getActiveBackendEvents($perPage);
            $pastEvents = $this->eventService->getPastBackendEvents($perPage);


            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'index', compact('events','upcomingEvents','pastEvents','isBsEnabled','companyDetail','filterParameters'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create_event');
        try{

            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $selectUser = ['id', 'name'];
            $users = $this->userRepository->getAllVerifiedEmployeeOfCompany($selectUser);
            $userIds = [];

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'create',
                compact('users','isBsEnabled','userIds','companyDetail')
            );
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(EventRequest $request)
    {
        $this->authorize('create_event');
        try {
            $validatedData = $request->validated();

            DB::beginTransaction();
            $eventDetail = $this->eventService->storeEvent($validatedData);
            DB::commit();
            if($eventDetail && $validatedData['notification'] == 1){

                $message = 'Your are invited to participate in '. ucfirst($eventDetail['title']);

                if(isset($eventDetail['end_date'])){
                    $message .=' starting from '.\App\Helpers\AppHelper::formatDateForView($eventDetail['start_date']). ' to '. \App\Helpers\AppHelper::formatDateForView($eventDetail['end_date']);
                }else{
                    $message .=' on '.\App\Helpers\AppHelper::formatDateForView($eventDetail['start_date']);
                }

                $this->sendNoticeNotification($eventDetail, $message, $validatedData['employee_id']);
            }
            return redirect()
                ->route('admin.event.index')
                ->with('success', __('message.event_create'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        try {
            $this->authorize('show_event');
            $select = ['*'];
            $eventDetail = $this->eventService->findEventDetailById($id, $select);

            $eventDetail->title = ucfirst($eventDetail->title);
            $eventDetail->location = ucfirst($eventDetail->location);
            $eventDetail->start_date = AppHelper::formatDateForView($eventDetail->start_date);
            $eventDetail->start_time = AppHelper::convertLeaveTimeFormat($eventDetail->start_time);
            $eventDetail->attachment = $eventDetail->attachment ? asset(Event::UPLOAD_PATH.$eventDetail->attachment):'';
            $eventDetail->end_date = isset($eventDetail->end_date) ? AppHelper::formatDateForView($eventDetail->end_date):'';
            $eventDetail->end_time = AppHelper::convertLeaveTimeFormat($eventDetail->end_time);
            $eventDetail->description = removeHtmlTags($eventDetail->description);
            $eventDetail->creator = $eventDetail->createdBy->name ?? 'Admin';
            return response()->json([
                'data' => $eventDetail,
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit($id)
    {
        $this->authorize('edit_event');
        try {
            $isBsEnabled = AppHelper::ifDateInBsEnabled();

            $with = ['eventDepartment','eventUser'];
            $selectUser = ['id', 'name'];
            $users = $this->userRepository->getAllVerifiedEmployeeOfCompany($selectUser);

            $eventDetail = $this->eventService->findEventDetailById($id, ['*'],$with);

            $departmentIds = [];
            foreach ($eventDetail->eventDepartment as $key => $value) {
                $departmentIds[] = $value->department_id;
            }
            $userIds = [];
            foreach ($eventDetail->eventUser as $key => $value) {
                $userIds[] = $value->user_id;
            }
            $select = ['name', 'id'];

            // Fetch users by selected departments
            $filteredUsers = !empty($departmentIds)
                ? $this->userRepository->getActiveEmployeesByDepartment($departmentIds, $select)
                : $users;


            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'edit', compact('eventDetail', 'companyDetail', 'users', 'userIds','departmentIds','isBsEnabled','filteredUsers'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(EventRequest $request, $id)
    {
        $this->authorize('edit_event');
        try {
            $validatedData = $request->validated();

            $previousEmployee = EventUser::where('event_id',$id)->get('user_id')->toArray();

            DB::beginTransaction();
            $eventDetail = $this->eventService->update($id, $validatedData);
            DB::commit();
            $previousEmployeeIds = array_column($previousEmployee, 'user_id');
            $removedIds = array_diff($previousEmployeeIds, $validatedData['employee_id']);
            $addedEmployeeIds = array_diff($validatedData['employee_id'], $previousEmployeeIds);

            $remainingEmployeeIds = array_intersect($previousEmployeeIds, $validatedData['employee_id']);

            if($eventDetail && $validatedData['notification'] == 1){

                $sendNotification = false;
                $today = date('Y-m-d H:i');
                $start = $eventDetail['start_date'].' '. $eventDetail['end_time'] ;
                if(isset($eventDetail['end_date'])){
                    $end = $eventDetail['end_date'] .' '. $eventDetail['end_time'];

                    if(strtotime($today) <= strtotime($end)){

                        $sendNotification = true;
                    }

                }else{

                    if(strtotime($today) <= strtotime($start)){

                        $sendNotification = true;
                    }

                }
                if($sendNotification){

                    // invitation
                    $message = 'Your are invited to participate in '. ucfirst($eventDetail['title']);

                    if(isset($eventDetail['end_date'])){
                        $message .=' starting from '.\App\Helpers\AppHelper::formatDateForView($eventDetail['start_date']). ' to '. \App\Helpers\AppHelper::formatDateForView($eventDetail['end_date']);
                    }else{
                        $message .=' on '.\App\Helpers\AppHelper::formatDateForView($eventDetail['start_date']);
                    }

                    $this->sendNoticeNotification($eventDetail, $message, $addedEmployeeIds);

                    // removal
                    $removeMassage = 'Sorry, we have cancelled your invitation in '. ucfirst($eventDetail['title']);

                    if(isset($eventDetail['end_date'])){
                        $removeMassage .=' starting from '.\App\Helpers\AppHelper::formatDateForView($eventDetail['start_date']). ' to '. \App\Helpers\AppHelper::formatDateForView($eventDetail['end_date']);
                    }else{
                        $removeMassage .=' on '.\App\Helpers\AppHelper::formatDateForView($eventDetail['start_date']);
                    }
                    $this->sendNoticeNotification($eventDetail, $removeMassage, $removedIds);


                    // change
                    $message = 'The event "' . ucfirst($eventDetail['title']) . '" that you are participating in has been updated';
                    $this->sendNoticeNotification($eventDetail, $message, $remainingEmployeeIds);

                }

            }
            return redirect()->route('admin.event.index')->with('success', __('message.event_update'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_event');
        try {
            DB::beginTransaction();
            $this->eventService->deleteEvent($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.event_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function removeImage($id)
    {
        $this->authorize('delete_event');
        try {
            DB::beginTransaction();
                $this->eventService->removeEventAttachment($id);
            DB::commit();
            return redirect()->back()->with('success',  __('message.image_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function updateEventStatus()
    {

        $this->eventService->updateStatus();
    }


    private function sendNoticeNotification($data, $description, $userIds)
    {
        $title =  __('message.event_notification');
        $notificationData['title'] = $title;
        $notificationData['type'] = 'event';
        $notificationData['user_id'] = $userIds;
        $notificationData['description'] = $description;
        $notificationData['notification_for_id'] = $data['id'];
        $notification = $this->notificationService->store($notificationData);
        if($notification){
            SMPushHelper::sendEventNotification($title, $description, $userIds);

        }

    }
}
