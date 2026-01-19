<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use App\Requests\Notice\NoticeRequest;
use App\Services\Notice\NoticeService;
use App\Traits\CustomAuthorizesRequests;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoticeController extends Controller
{
    use CustomAuthorizesRequests;

    private $view = 'admin.notice.';


    public function __construct(protected CompanyRepository $companyRepo,
                                protected UserRepository    $userRepo,
                                protected NoticeService     $noticeService,
                                protected BranchRepository  $branchRepository)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('list_notice');
        try {
            $filterParameters = [
                'employee_id' => $request->employee_id ?? null,
                'department_id' => $request->department_id ?? null,
                'publish_date_from' => $request->publish_date_from ?? null,
                'publish_date_to' => $request->publish_date_to ?? null,
                'branch_id' => $request->branch_id ?? null,
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }


            $select = ['*'];
            $with = ['noticeReceiversDetail'];
            $notices = $this->noticeService->getAllCompanyNotices($filterParameters, $select, $with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);

            return view($this->view . 'index', compact('notices', 'filterParameters','companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create_notice');
        try {

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


    public function store(NoticeRequest $request)
    {
        $this->authorize('create_notice');
        try {
            $validatedData = $request->validated();

            DB::beginTransaction();
            $notice = $this->noticeService->store($validatedData);
            DB::commit();
            if ($notice  && $validatedData['notification'] == 1) {
                $userIds = $this->getUserIdsForNoticeNotification($validatedData['receiver']['employee']);
                $this->sendNoticeNotification(ucfirst($validatedData['title']), removeHtmlTags($notice['description']), $userIds);
            }
            return redirect()
                ->route('admin.notices.index')
                ->with('success', __('message.notice_create_sent'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    private function getUserIdsForNoticeNotification($validatedData)
    {
        try {
            $userIds = [];
            foreach ($validatedData as $key => $value) {
                $userIds[] = $value;
            }
            return $userIds;
        } catch (Exception $ex) {
            return redirect()->back()->with('danger', $ex->getMessage());
        }
    }

    private function sendNoticeNotification($title, $description, $userIds)
    {
        SMPushHelper::sendNoticeNotification($title, $description, $userIds);
    }

    public function show($id)
    {
        try {
            $this->authorize('show_notice');
            $select = ['description', 'title'];
            $notice = $this->noticeService->findOrFailNoticeDetailById($id, $select);
            $notice->description = removeHtmlTags($notice->description);
            $notice->title = ucfirst($notice->title);
            return response()->json([
                'data' => $notice,
            ]);
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function edit($id)
    {
        $this->authorize('edit_notice');
        try {
            $with = ['noticeReceiversDetail','noticeDepartments'];
            $selectNotice = ['*'];
            $noticeDetail = $this->noticeService->findOrFailNoticeDetailById($id, $selectNotice, $with);
            $receiverUserIds = [];
            foreach ($noticeDetail->noticeReceiversDetail as $key => $value) {
                $receiverUserIds[] = $value->notice_receiver_id;
            }


            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);

            return view($this->view . 'edit', compact('noticeDetail', 'receiverUserIds','companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(NoticeRequest $request, $id)
    {
        $this->authorize('edit_notice');
        try {

            $validatedData = $request->validated();

            $noticeDetail = $this->noticeService->findOrFailNoticeDetailById($id);
            DB::beginTransaction();
            $updateNotice = $this->noticeService->update($noticeDetail, $validatedData);
            DB::commit();
            if ($updateNotice  && $validatedData['notification'] == 1) {
                $userIds = $this->getUserIdsForNoticeNotification($validatedData['receiver']['employee']);
                $this->sendNoticeNotification(ucfirst($validatedData['title']), removeHtmlTags($validatedData['description']), $userIds);
            }
            return redirect()->route('admin.notices.index')->with('success', __('message.notice_update_sent'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit_notice');
        try {
            DB::beginTransaction();
            $this->noticeService->changeNoticeStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.notice_status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_notice');
        try {
            DB::beginTransaction();
            $this->noticeService->deleteNotice($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.notice_deleted'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function sendNotice($noticeId)
    {
        $this->authorize('send_notice');
        try {
            $with = ['noticeReceiversDetail'];
            $select = ['*'];
            $noticeDetail = $this->noticeService->findOrFailNoticeDetailById($noticeId, $select, $with);
            $userIds = $this->getUserIdsForNoticeNotification($noticeDetail->noticeReceiversDetail);
            $this->sendNoticeNotification(ucfirst($noticeDetail->title), removeHtmlTags($noticeDetail->description), $userIds);
            DB::beginTransaction();
            $validatedData['is_active'] = 1;
            $validatedData['notice_publish_date'] = Carbon::now()->format('Y-m-d H:i:s');
            $this->noticeService->updatePublishDateAndStatus($noticeDetail, $validatedData);
            DB::commit();
            return redirect()->back()->with('success', __('message.notice_sent'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getFile());
        }
    }

}
