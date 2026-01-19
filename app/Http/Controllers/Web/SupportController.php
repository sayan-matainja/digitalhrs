<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\Support;
use App\Repositories\CompanyRepository;
use App\Repositories\SupportRepository;
use App\Services\Notification\NotificationService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportController extends Controller
{
    use CustomAuthorizesRequests;
    private $view ='admin.support.';


    public function __construct(protected SupportRepository $supportRepo, protected NotificationService $notificationService, protected CompanyRepository $companyRepository)
    {}

    public function getAllQueriesPaginated(Request $request)
    {
        $this->authorize('view_query_list');
        try {
            $filterParameters = [
                'is_seen' => ($request->is_seen) ?? null,
                'status' => $request->status ?? null,
                'query_from' => $request->query_from ?? null,
                'query_to' => $request->query_to ?? null,
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
            ];
            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }
            $with = [
                'createdBy:id,name,branch_id,department_id,phone',
                'createdBy.branch:id,name',
                'createdBy.department:id,dept_name',
                'departmentQuery:id,dept_name'
            ];
            $select=['*'];
            $supportQueries = $this->supportRepo->getAllQueryDetail($filterParameters,$select,$with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'index',compact('supportQueries','filterParameters','companyDetail'));
        } catch (\Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function changeIsSeenStatus($queryId)
    {
        $this->authorize('show_query_detail');
        try{
            $queryDetail = $this->supportRepo->findDetailById($queryId);
            if(!$queryDetail){
                throw new \Exception(__('message.query_not_found'),404);
            }
            $this->supportRepo->changeStatusToSeen($queryDetail);
            return AppHelper::sendSuccessResponse('success');
        }catch(\Exception $exception){
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function changeQueryStatus(Request $request, $queryId)
    {
        $this->authorize('update_query_status');
        try{
            $validatedData = $request->validate([
               'status'=> ['required',Rule::in(Support::STATUS)]
            ]);
            $supportDetail = $this->supportRepo->findDetailById($queryId);
            $update = $this->supportRepo->changeQueryStatus($supportDetail,$validatedData['status']);
            if($update){
                    $notificationData['title'] = __('message.support_notification');
                    $notificationData['type'] = 'support';
                    $notificationData['user_id'] = [$supportDetail->created_by];
                    $notificationData['description'] = __('message.support_message',['title'=>$supportDetail->title,'status'=>ucwords(removeSpecialChar($validatedData['status']))]);
                    $notificationData['notification_for_id'] = $supportDetail->id;
                    $notification = $this->notificationService->store($notificationData);
                    if($notification){
                        $this->sendSupportNotification(
                            $notification->title,
                            $notification->description,
                            $notificationData['user_id'],
                            $supportDetail->id);
                    }
            }
            return redirect()->back()->with('success', __('message.query_status_change'));
        }catch (\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete($queryId)
    {
        $this->authorize('delete_query');
        try{
            $queryDetail = $this->supportRepo->findDetailById($queryId);
            $this->supportRepo->delete($queryDetail);
            return redirect()->back()->with('success', __('message.query_delete'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    private function sendSupportNotification($title, $message, $userIds, $queryId)
    {
        $notificationData['title'] = __('message.support_notification');
        $notificationData['type'] = 'support';
        $notificationData['user_id'] = $userIds;
        $notificationData['description'] = $message;
        $notificationData['notification_for_id'] = $queryId;
        $notification = $this->notificationService->store($notificationData);
        if($notification){
            SMPushHelper::sendSupportNotification($title,$message,$userIds,$queryId);
        }

    }
}
