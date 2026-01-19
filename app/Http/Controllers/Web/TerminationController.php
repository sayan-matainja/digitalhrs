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
use App\Requests\Termination\TerminationRequest;
use App\Services\Notification\NotificationService;
use App\Services\Termination\TerminationService;
use App\Services\Termination\TerminationTypeService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;

class TerminationController extends Controller
{
    use CustomAuthorizesRequests;
    private string $view = 'admin.terminationManagement.termination.';

    public function __construct(
        protected TerminationService $terminationService, protected TerminationTypeService $terminationTypeService,
        protected UserRepository $userRepository, protected NotificationService $notificationService, protected CompanyRepository $companyRepository,
        protected DepartmentRepository $departmentRepository
    ){}

    /**
     * Display a listing of the resource.
     *
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_termination');
        try{

            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'department_id' => $request->department_id ?? null,
                'termination_date' => $request->termination_date ?? null,
                'termination_type_id' => $request->termination_type_id ?? null,
            ];
            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $select = ['*'];
            $with = ['terminationType:id,title','employee:id,name'];
            $terminationLists = $this->terminationService->getAllTerminationPaginated($filterParameters,$select,$with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view.'index', compact('terminationLists','companyDetail','filterParameters'));
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
        $this->authorize('create_termination');

        try{

            $terminationStatus = TerminationStatusEnum::cases();
            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view.'create', compact('companyDetail','terminationStatus','isBsEnabled'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @param TerminationRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function store(TerminationRequest $request)
    {
        $this->authorize('create_termination');

        try{
            $validatedData = $request->validated();

            DB::beginTransaction();
            $terminationData = $this->terminationService->saveTerminationDetail($validatedData);
            DB::commit();

            if($terminationData && $validatedData['status'] == TerminationStatusEnum::approved->value)
            {
                $this->sendTerminationNotification($terminationData);

            }
            return redirect()->route('admin.termination.index')->with('success',__('message.add_termination') );
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
        $this->authorize('show_termination');

        try{
            $select = ['*'];
            $with = ['terminationType:id,title','employee:id,name','department:id,dept_name','branch:id,name'];
            $terminationDetail = $this->terminationService->findTerminationById($id,$select,$with);

            return view($this->view.'show', compact('terminationDetail'));
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
        $this->authorize('update_termination');
        try{
            $terminationDetail = $this->terminationService->findTerminationById($id);
            $terminationStatus = TerminationStatusEnum::cases();
            $isBsEnabled = AppHelper::ifDateInBsEnabled();

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            // Fetch users by selected departments
            $filteredDepartment = isset($terminationDetail->branch_id)
                ? $this->departmentRepository->getAllActiveDepartmentsByBranchId($terminationDetail->branch_id, [], ['id', 'dept_name'])
                : [];

            $selectUser = ['name', 'id'];
            $filteredUsers = isset($terminationDetail->department_id)
                ? $this->userRepository->getActiveEmployeeOfDepartment($terminationDetail->department_id, $selectUser)
                : [];

            $selectType = ['title', 'id'];
            $filteredTypes = isset($terminationDetail->branch_id)
                ? $this->terminationTypeService->getActiveBranchTerminationType($terminationDetail->branch_id, $selectType)
                : [];

            return view($this->view.'edit', compact('terminationDetail','companyDetail','terminationStatus','filteredUsers','filteredTypes','filteredDepartment','isBsEnabled'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TerminationRequest $request
     * @param int $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(TerminationRequest $request, $id): RedirectResponse
    {
        $this->authorize('update_termination');
        try{
            $validatedData = $request->validated();

            DB::beginTransaction();
            $this->terminationService->updateTerminationDetail($id,$validatedData);
            DB::commit();
            return redirect()->route('admin.termination.index')
                ->with('success', __('message.update_termination'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_termination');
        try{
            DB::beginTransaction();
            $this->terminationService->deleteTermination($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_termination'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param $terminationId
     * @return RedirectResponse
     * @throws AuthorizationException
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function updateTerminationStatus(Request $request, $terminationId)
    {
        $this->authorize('update_termination');
        try {
            $validatedData = $request->validate([
                'status' => ['required', 'string', Rule::in(array_column(TerminationStatusEnum::cases(), 'value'))],
                'admin_remark' => ['nullable', 'required_if:status,'.TerminationStatusEnum::cancelled->value, 'string', 'min:10'],
            ]);


            $terminationData = $this->terminationService->updateStatus($terminationId, $validatedData);

            if($terminationData && $validatedData['status'] == TerminationStatusEnum::approved->value)
            {
               $this->sendTerminationNotification($terminationData);

            }

            return redirect()
                ->route('admin.termination.index')
                ->with('success', __('message.status_update'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws MessagingException
     * @throws FirebaseException
     * @throws Exception
     */
    public function sendTerminationNotification($terminationData){
        $notificationData = [
            'title' => 'Termination Notification',
            'type' => 'termination',
            'user_id' => [$terminationData->employee_id],
            'description' => 'You are terminated from your position, effective from ' . date('M d Y', strtotime($terminationData->termination_date)).'. Please ensure all company devices and access credentials have been submitted. Refer to termination checklist in HR.',
            'notification_for_id' => $terminationData->id,
        ];

        $notification = $this->notificationService->store($notificationData);

        if($notification){
            SMPushHelper::sendResignationStatusNotification($notification->title, $notification->description,$terminationData->employee_id);
        }

    }


    /**
     * @param $branchId
     * @return JsonResponse
     */
    public function getBranchTerminationData($branchId)
    {
        try {

            $types = $this->terminationTypeService->getActiveBranchTerminationType($branchId, ['id','title']);
            $departments = $this->departmentRepository->getAllActiveDepartmentsByBranchId($branchId,[], ['id','dept_name']);

            return response()->json([
                'types' => $types,
                'departments' => $departments,
            ]);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }

    }

}
