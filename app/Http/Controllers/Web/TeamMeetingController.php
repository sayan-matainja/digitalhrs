<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\MeetingParticipatorDetail;
use App\Models\TeamMeeting;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\TeamMeeting\TeamMeetingRequest;
use App\Services\Notification\NotificationService;
use App\Services\TeamMeeting\TeamMeetingService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeamMeetingController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.teamMeeting.';

    public function __construct(protected CompanyRepository  $companyRepo,
                                protected UserRepository     $userRepo,
                                protected TeamMeetingService $teamMeetingService,
                                protected DepartmentRepository $departmentRepository,
                                protected NotificationService $notificationService,
    protected BranchRepository $branchRepository)
    {}

    public function index(Request $request)
    {
        $this->authorize('list_team_meeting');
        try {
            $filterParameters = [
                'company_id' => AppHelper::getAuthUserCompanyId(),
                'participator' => $request->participator ?? null,
                'meeting_from' => $request->meeting_from ?? null,
                'meeting_to' => $request->meeting_to ?? null,
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
            ];
            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }
            $select = ['*'];
            $with = ['teamMeetingParticipator'];

            $teamMeetings = $this->teamMeetingService->getAllCompanyTeamMeetings($filterParameters,$select, $with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);

            return view($this->view . 'index', compact('teamMeetings','filterParameters','companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create_team_meeting');
        try{
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);

            return view($this->view . 'create',
                compact('companyDetail')
            );
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(TeamMeetingRequest $request)
    {
        $this->authorize('create_team_meeting');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $teamMeeting = $this->teamMeetingService->store($validatedData);
            DB::commit();
            if ($teamMeeting && $validatedData['notification'] == 1) {
                $userIds = $this->getUserIdsForTeamMeetingNotification($validatedData['participator']);
                $this->sendTeamMeetingNotification(
                    ucfirst($validatedData['title']),
                    'You are invited for team meeting at '. ($validatedData['venue']).' on '.
                    ( \App\Helpers\AppHelper::formatDateForView($validatedData['meeting_date']) .' at ' .AttendanceHelper::changeTimeFormatForAttendanceView($validatedData['meeting_start_time'])),
                    $userIds,
                    $teamMeeting->id
                );
            }
            return redirect()
                ->route('admin.team-meetings.index')
                ->with('success', __('message.team_meeting_create'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        try {
            $this->authorize('show_team_meeting');
            $select = ['*'];
            $teamMeeting = $this->teamMeetingService->findOrFailTeamMeetingDetailById($id, $select);

            $teamMeeting->title = ucfirst($teamMeeting->title);
            $teamMeeting->venue = ucfirst($teamMeeting->venue);
            $teamMeeting->meeting_date = AppHelper::formatDateForView($teamMeeting->meeting_date);
            $teamMeeting->image = $teamMeeting->image ? asset(TeamMeeting::UPLOAD_PATH.$teamMeeting->image):'';
            $teamMeeting->time = AttendanceHelper::changeTimeFormatForAttendanceView($teamMeeting->meeting_start_time);
            $teamMeeting->description = removeHtmlTags($teamMeeting->description);
            $teamMeeting->meeting_published_at = convertDateTimeFormat($teamMeeting->meeting_published_at);
            $teamMeeting->creator = $teamMeeting->createdBy->name ?? 'Admin';

            return response()->json([
                'data' => $teamMeeting,
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function edit($id)
    {
        $this->authorize('edit_team_meeting');
        try {
            $departmentIds = [];
            $with = ['teamMeetingParticipator'];
            $selectMeeting = ['*'];
            $teamMeetingDetail = $this->teamMeetingService->findOrFailTeamMeetingDetailById($id, $selectMeeting, $with);
            if((AppHelper::ifDateInBsEnabled())){
                $teamMeetingDetail->meeting_date =  AppHelper::dateInYmdFormatEngToNep($teamMeetingDetail->meeting_date);
            }
            $participatorIds = [];
            foreach ($teamMeetingDetail->teamMeetingParticipator as $key => $value) {
                $participatorIds[] = $value->meeting_participator_id ;
            }

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);

            foreach ($teamMeetingDetail->meetingDepartment as $key => $value) {
                $departmentIds[] = $value->department_id;
            }
            // Fetch users by selected departments
            $filteredDepartment = isset($teamMeetingDetail->branch_id)
                ? $this->departmentRepository->getAllActiveDepartmentsByBranchId($teamMeetingDetail->branch_id,[], ['id','dept_name'])
                : [];
            return view($this->view . 'edit', compact('teamMeetingDetail', 'participatorIds','companyDetail','filteredDepartment','departmentIds'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(TeamMeetingRequest $request, $id)
    {
        $this->authorize('edit_team_meeting');
        try {
            $validatedData = $request->validated();

            $teamMeetingDetail = $this->teamMeetingService->findOrFailTeamMeetingDetailById($id);
            $previousEmployee = MeetingParticipatorDetail::where('team_meeting_id',$id)->get('meeting_participator_id')->toArray();

            DB::beginTransaction();
                $updateTeamMeeting = $this->teamMeetingService->update($teamMeetingDetail, $validatedData);
            DB::commit();

            $previousEmployeeIds = array_column($previousEmployee, 'meeting_participator_id');
            $userIds = $this->getUserIdsForTeamMeetingNotification($validatedData['participator']);

            $removedIds = array_diff($previousEmployeeIds, $userIds);
            $addedEmployeeIds = array_diff($userIds, $previousEmployeeIds);

            $remainingEmployeeIds = array_intersect($previousEmployeeIds, $userIds);

            if($updateTeamMeeting && $validatedData['notification'] == 1){

                $today = date('Y-m-d H:i');
                $start = $updateTeamMeeting['meeting_date'].' '. $updateTeamMeeting['meeting_start_time'] ;

                if(strtotime($today) <= strtotime($start)) {

                    // for invitation
                    $this->sendTeamMeetingNotification(
                        ucfirst($teamMeetingDetail->title),
                        'You are invited for team meeting at '. ($teamMeetingDetail->venue).' on '.
                        ( \App\Helpers\AppHelper::formatDateForView($teamMeetingDetail->meeting_date) .' at ' .AttendanceHelper::changeTimeFormatForAttendanceView($teamMeetingDetail->meeting_start_time)),
                        $addedEmployeeIds,
                        $updateTeamMeeting->id
                    );


                    // remove notification
                    $this->sendTeamMeetingNotification(
                        ucfirst($teamMeetingDetail->title),
                        'Sorry, we have cancelled your participation in team meeting at '. ($teamMeetingDetail->venue).' on '.
                        ( \App\Helpers\AppHelper::formatDateForView($teamMeetingDetail->meeting_date) .' at ' .AttendanceHelper::changeTimeFormatForAttendanceView($teamMeetingDetail->meeting_start_time)),
                        $removedIds,
                        $updateTeamMeeting->id
                    );

                    // change notification
                    $this->sendTeamMeetingNotification(
                        ucfirst($teamMeetingDetail->title),
                        '"The team meeting in which you are participating has been updated"',
                        $remainingEmployeeIds,
                        $updateTeamMeeting->id
                    );

                }
            }

            return redirect()->route('admin.team-meetings.index')->with('success', __('message.team_meeting_update'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_team_meeting');
        try {
            DB::beginTransaction();
            $this->teamMeetingService->deleteTeamMeeting($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.team_meeting_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function removeImage($id)
    {
        $this->authorize('delete_team_meeting');
        try {
            DB::beginTransaction();
                $this->teamMeetingService->removeMeetingImage($id);
            DB::commit();
            return redirect()->back()->with('success',  __('message.image_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    private function getUserIdsForTeamMeetingNotification($validatedData): array
    {
        $userIds = [];
        foreach ($validatedData as $key => $value) {

            $userIds[] = $value['meeting_participator_id'];
        }
        return $userIds;
    }

    /**
     * @throws Exception
     */
    private function sendTeamMeetingNotification($title, $message, $userIds, $teamMeetingId)
    {
        $notificationData['title'] = __('message.team_meeting_notification');
        $notificationData['type'] = 'meeting';
        $notificationData['user_id'] = $userIds;
        $notificationData['description'] = $message;
        $notificationData['notification_for_id'] = $teamMeetingId;
        $notification = $this->notificationService->store($notificationData);
        Log::info(json_encode($notification));
        if($notification){
            SMPushHelper::sendTeamMeetingNotification($title,$message,$userIds,$teamMeetingId);

        }

    }
}
