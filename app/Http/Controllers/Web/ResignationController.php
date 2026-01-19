<?php

namespace App\Http\Controllers\Web;

use App\Enum\ResignationStatusEnum;
use App\Enum\TerminationStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\Resignation\ResignationRequest;
use App\Services\Notification\NotificationService;
use App\Services\Resignation\ResignationService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;


class ResignationController extends Controller
{
    use CustomAuthorizesRequests;
    private string $view = 'admin.resignation.';

    public function __construct(
        protected ResignationService $resignationService,
        protected UserRepository $userRepository,
        protected NotificationService $notificationService,
        protected CompanyRepository $companyRepository,
        protected DepartmentRepository $departmentRepository
    ){}

    /**
     * Display a listing of the resource.
     *
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_resignation');
        try{

            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'department_id' => $request->department_id ?? null,
                'resignation_date' => $request->resignation_date ?? null,
            ];
            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }
            $select = ['*'];
            $with = ['employee:id,name','branch:id,name','department:id,dept_name'];
            $resignationLists = $this->resignationService->getAllResignationPaginated($filterParameters, $select,$with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view.'index', compact('resignationLists','companyDetail','filterParameters'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        $this->authorize('create_resignation');

        try{
            $resignationStatus = ResignationStatusEnum::cases();

            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view.'create', compact('resignationStatus','companyDetail','isBsEnabled'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @param ResignationRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function store(ResignationRequest $request)
    {
        $this->authorize('create_resignation');

        try{
            $validatedData = $request->validated();

            DB::beginTransaction();
            $resignationDetail = $this->resignationService->saveResignationDetail($validatedData);
            DB::commit();
            if($resignationDetail){
                $employee = $this->userRepository->findUserDetailById($resignationDetail->employee_id,['id','supervisor_id','name']);

                $notificationData = [
                    'title' => 'Resignation Request',
                    'type' => 'resignation',
                    'user_id' => [$resignationDetail->employee_id],
                    'description' => $employee->name.' has requested resignation on ' . date('M d Y', strtotime($resignationDetail->resignation_date)) .' effective from '. date('M d Y', strtotime($resignationDetail->last_working_day)).'.' ,
                    'notification_for_id' => $resignationDetail->id,
                ];

                $notification = $this->notificationService->store($notificationData);

                if($notification){
                    $this->sendResignationStatusNotification($notification,$employee->supervisor_id);
                }

            }
            return redirect()->route('admin.resignation.index')->with('success',__('message.add_resignation') );
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @throws AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('show_resignation');

        try{
            $select = ['*'];
            $with = ['employee:id,name'];
            $resignationDetail = $this->resignationService->findResignationById($id,$select,$with);

            return view($this->view.'show', compact('resignationDetail'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @throws AuthorizationException
     */
    public function edit($id)
    {
        $this->authorize('update_resignation');
        try{
            $resignationDetail = $this->resignationService->findResignationById($id);
            $resignationStatus = TerminationStatusEnum::cases();

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            // Fetch users by selected departments
            $filteredDepartment = isset($resignationDetail->branch_id)
                ? $this->departmentRepository->getAllActiveDepartmentsByBranchId($resignationDetail->branch_id, [], ['id', 'dept_name'])
                : [];

            $select = ['name', 'id'];
            $filteredUsers = isset($resignationDetail->department_id)
                ? $this->userRepository->getActiveEmployeeOfDepartment($resignationDetail->department_id, $select)
                : [];


            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            return view($this->view.'edit', compact('resignationDetail','resignationStatus','filteredUsers','filteredDepartment','companyDetail','isBsEnabled'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ResignationRequest $request
     * @param int $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(ResignationRequest $request, $id): RedirectResponse
    {
        $this->authorize('update_resignation');
        try{
            $validatedData = $request->validated();
            DB::beginTransaction();
            $resignationDetail = $this->resignationService->updateResignationDetail($id,$validatedData);
            DB::commit();

            if($resignationDetail){

                $notificationData = [
                    'title' => 'Resignation Status Update',
                    'type' => 'resignation',
                    'user_id' => [$resignationDetail['employee_id']],
                    'description' => 'Your resignation requested on ' . date('M d Y', strtotime($resignationDetail['resignation_date'])) . ' is ' . ucfirst($resignationDetail['status']).'. Reason: '.$resignationDetail['admin_remark'],
                    'notification_for_id' => $id,
                ];

                $notification = $this->notificationService->store($notificationData);

                if($notification){
                    $this->sendResignationStatusNotification($notification,$resignationDetail['employee_id']);
                }
            }

            return redirect()->route('admin.resignation.index')
                ->with('success', __('message.update_resignation'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function delete($id)
    {
        $this->authorize('delete_resignation');
        try{
            DB::beginTransaction();
            $this->resignationService->deleteResignation($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_resignation'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param $resignationId
     * @return RedirectResponse
     * @throws AuthorizationException
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function updateResignationStatus(Request $request, $resignationId)
    {
        $this->authorize('update_resignation');

        try {

            $validatedData = $request->validate([
                'status' => ['required', 'string', Rule::in(array_column(ResignationStatusEnum::cases(), 'value'))],
                'admin_remark' => ['required', 'required_if:status,'.ResignationStatusEnum::cancelled->value, 'string', 'min:10'],
            ]);

            DB::beginTransaction();
            $resignationDetail = $this->resignationService->updateStatus($resignationId, $validatedData);
            DB::commit();

            if($resignationDetail){
                $notificationData = [
                    'title' => 'Resignation Status Update',
                    'type' => 'Resignation',
                    'user_id' => [$resignationDetail->employee_id],
                    'description' => 'Your resignation requested on ' . date('M d Y', strtotime($resignationDetail->resignation_date)) . ' is ' . ucfirst($validatedData['status']),
                    'notification_for_id' => $resignationId,
                ];

                $notification = $this->notificationService->store($notificationData);

                if($notification){
                    $this->sendResignationStatusNotification($notification,$resignationDetail->employee_id);
                }
            }

//            if(($validatedData['status'] == ResignationStatusEnum::approved->value) && strtotime(date('Y-m-d')) == strtotime($resignationDetail->last_working_day)){
//                $this->userRepository->deactivateUserAccount($resignationDetail->employee_id);
//            }

            return redirect()
                ->route('admin.resignation.index')
                ->with('success', __('message.status_update'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @param $notificationData
     * @param $userId
     * @return void
     * @throws FirebaseException
     * @throws MessagingException
     * @throws Exception
     */
    private function sendResignationStatusNotification($notificationData, $userId): void
    {
        SMPushHelper::sendResignationStatusNotification($notificationData->title, $notificationData->description,$userId);
    }
}
