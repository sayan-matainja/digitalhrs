<?php

namespace App\Http\Controllers\Web;

use App\Enum\TrainerTypeEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Mail\SendTrainingChangeMail;
use App\Mail\SendTrainingMail;
use App\Mail\SendTrainingRemovalMail;
use App\Models\Company;
use App\Models\EmployeeTraining;
use App\Models\TrainingInstructor;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\TrainingManagement\TrainingRequest;
use App\Services\Notification\NotificationService;
use App\Services\TrainingManagement\TrainerService;
use App\Services\TrainingManagement\TrainingService;
use App\Services\TrainingManagement\TrainingTypeService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

class TrainingController extends Controller
{
    use CustomAuthorizesRequests;
    private string $view = 'admin.trainingManagement.training.';

    public function __construct(
        protected TrainingService $trainingService, protected TrainingTypeService $trainingTypeService,
        protected UserRepository $userRepository, protected TrainerService $trainerService, protected BranchRepository $branchRepository,
        protected DepartmentRepository $departmentRepository, protected CompanyRepository $companyRepository, protected NotificationService $notificationService
    ){}

    /**
     * Display a listing of the resource.
     *
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_training');
        try{

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

            $this->updateTrainingStatus();
            $select = ['*'];
            $with = ['trainingType:id,title','employeeTraining.employee:id,name','branch:id,name'];
            $trainingLists = $this->trainingService->getAllTrainingPaginated($filterParameters, $select,$with);
            $trainerTypes = TrainerTypeEnum::cases();


            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view.'index', compact('trainingLists','trainerTypes','companyDetail','filterParameters'));
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
        $this->authorize('create_training');

        try{
            $departmentIds = [];
            $employeeIds = [];
            $companyId = AppHelper::getAuthUserCompanyId();

            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $selectBranch = ['id','name'];
            $trainerTypes = TrainerTypeEnum::cases();
            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);

            return view($this->view.'create', compact('trainerTypes','branch','isBsEnabled','employeeIds','departmentIds'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @param TrainingRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(TrainingRequest $request)
    {
        $this->authorize('create_training');

        try{
            $validatedData = $request->validated();

            DB::beginTransaction();
            $trainingDetail = $this->trainingService->saveTrainingDetail($validatedData);
            DB::commit();


            if($trainingDetail && $validatedData['notification'] == 1){
                // notification to members
                $type = $this->trainingTypeService->findTrainingTypeById($trainingDetail['training_type_id']);
                $message = 'You are cordially invited to attend the '. ucfirst($type->title).' training';

                $this->sendNoticeNotification( $message,$trainingDetail, $validatedData['employee_id']);


                // notification to trainers
                $trainerAddMessage = 'You are cordially invited to serve as a trainer for the upcoming '. ucfirst($type->title) . ' training ';
                $this->sendTrainerEmailAndNotification($type->title, $validatedData['trainer_id'],$trainingDetail,$trainerAddMessage, 'invitation' );


            }

            return redirect()->route('admin.training.index')->with('success',__('message.add_training') );
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id

     */
    public function show($id)
    {
        $this->authorize('show_training');

        try{
            $select = ['*'];
            $with = ['trainingType:id,title','branch:id,name','trainingInstructor.trainer.employee:id,name','trainingDepartment.department:id,dept_name','createdBy:id,name', 'updatedBy:id,name','employeeTraining.employee:id,name'];
            $trainingDetail = $this->trainingService->findTrainingById($id,$select,$with);
            $trainerTypes = TrainerTypeEnum::cases();
            $departmentIds = [];
            $employeeIds = [];
            return view($this->view.'show', compact('trainingDetail','trainerTypes','departmentIds','employeeIds'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id)
    {
        $this->authorize('update_training');
        try{
            $with = ['employeeTraining','trainingDepartment','trainingInstructor.trainer.employee:id,name'];
            $trainingDetail = $this->trainingService->findTrainingById($id,['*'],$with);
            $companyId = AppHelper::getAuthUserCompanyId();

            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $selectBranch = ['id','name'];
            $trainerTypes = TrainerTypeEnum::cases();

            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);

            $selectUser = ['id', 'name'];
            $users = $this->userRepository->getAllVerifiedEmployeeOfCompany($selectUser);
            $employeeIds = [];
            foreach ($trainingDetail->employeeTraining as $key => $value) {
                $employeeIds[] = $value->employee_id;
            }

            $departmentIds = [];
            foreach ($trainingDetail->trainingDepartment as $key => $value) {
                $departmentIds[] = $value->department_id;
            }
            // Fetch users by selected departments
            $filteredDepartment = isset($trainingDetail->branch_id)
                ? $this->departmentRepository->getAllActiveDepartmentsByBranchId($trainingDetail->branch_id,[], ['id','dept_name'])
                : [];

            $select = ['name', 'id'];
            // Fetch users by selected departments
            $filteredUsers = !empty($departmentIds)
                ? $this->userRepository->getActiveEmployeesByDepartment($departmentIds, $select)
                : $users;

            return view($this->view.'edit', compact('trainingDetail','isBsEnabled','trainerTypes','branch','employeeIds','filteredUsers','departmentIds','filteredDepartment'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TrainingRequest $request
     * @param int $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(TrainingRequest $request, $id): RedirectResponse
    {
        $this->authorize('update_training');
        try{
            $previousEmployee = [];
            $previousTrainers = [];

            $validatedData = $request->validated();

            if($validatedData['notification'] == 1){
                $previousEmployee = EmployeeTraining::where('training_id',$id)->get('employee_id')->toArray();

                $previousTrainers = TrainingInstructor::where('training_id',$id)->get('trainer_id')->toArray();
            }

            DB::beginTransaction();
            $trainingDetail = $this->trainingService->updateTrainingDetail($id,$validatedData);
            DB::commit();

            if($trainingDetail && $validatedData['notification'] == 1){

                $previousEmployeeIds = array_column($previousEmployee, 'employee_id');
                $removedIds = array_diff($previousEmployeeIds, $validatedData['employee_id']);
                $addedEmployeeIds = array_diff($validatedData['employee_id'], $previousEmployeeIds);

                $remainingEmployeeIds = array_intersect($previousEmployeeIds, $validatedData['employee_id']);


                // trainers
                $previousTrainerIds = array_column($previousTrainers, 'trainer_id');
                $removedTrainerIds = array_diff($previousTrainerIds, $validatedData['trainer_id']);
                $addedTrainerIds = array_diff($validatedData['employee_id'], $previousTrainerIds);
                $remainingTrainerIds = array_intersect($previousTrainerIds, $validatedData['trainer_id']);


                $sendNotification = false;
                $today = date('Y-m-d H:i');
                $start = $trainingDetail['start_date'].' '. $trainingDetail['end_time'] ;
                if(isset($trainingDetail['end_date'])){
                    $end = $trainingDetail['end_date'] .' '. $trainingDetail['end_time'];

                    if(strtotime($today) <= strtotime($end)){

                        $sendNotification = true;
                    }
                }else{
                    if(strtotime($today) <= strtotime($start)){
                        $sendNotification = true;
                    }
                }

                if($sendNotification) {

                    $type = $this->trainingTypeService->findTrainingTypeById($trainingDetail['training_type_id']);
                    // add notification
                    $message = 'You are cordially invited to attend the '. ucfirst($type->title).' training';

                    $this->sendNoticeNotification( $message,$trainingDetail, $addedEmployeeIds);


                    //remove notification
                    $removeMassage = 'Sorry, we have cancelled your invitation in ' . ucfirst($type->title). ' training';

                    $this->sendNoticeNotification( $removeMassage,$trainingDetail, $removedIds);

                    // change notification
                    $changeMassage = 'The training "' . ucfirst($type->title) . '" that you are participating in has been updated';

                    $this->sendNoticeNotification( $changeMassage,$trainingDetail, $remainingEmployeeIds);

                    // notification to trainers

                    // invitation
                    if($addedTrainerIds){
                        $trainerAddMessage = 'You are cordially invited to serve as a trainer for the upcoming '. ucfirst($type->title) . ' training ';
                        $this->sendTrainerEmailAndNotification($type->title, $addedTrainerIds,$trainingDetail,$trainerAddMessage, 'invitation' );
                    }

                    // change
                    if($remainingTrainerIds){
                        $trainingChangeMessage = 'There has been an update to training '. ucfirst($type->title) .' where you are assigned as trainer.';
                        $this->sendTrainerEmailAndNotification($type->title, $remainingTrainerIds,$trainingDetail,$trainingChangeMessage, 'change' );
                    }


                    //remove
                    if($removedTrainerIds){
                        $trainerRemoveMessage = 'Your trainer assignment for the '. ucfirst($type->title) .' training has been removed. ' .
                            'We appreciate your willingness to participate and will keep you in mind for future opportunities.';
                        $this->sendTrainerEmailAndNotification($type->title, $removedTrainerIds,$trainingDetail,$trainerRemoveMessage, 'remove' );
                    }


                }
            }
            return redirect()->route('admin.training.index')
                ->with('success', __('message.update_training'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_training');
        try{
            DB::beginTransaction();
            $this->trainingService->deleteTraining($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_training'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function updateTrainingStatus()
    {

        $this->trainingService->updateStatus();
    }

    private function sendNoticeNotification($message, $data, $userIds)
    {
        if(isset($data['end_date'])){
            $message .=' scheduled  from '.\App\Helpers\AppHelper::formatDateForView($data['start_date']). ' to '. \App\Helpers\AppHelper::formatDateForView($data['end_date']);
        }else{
            $message .=' scheduled on '.\App\Helpers\AppHelper::formatDateForView($data['start_date']);
        }

        $notificationData['title'] = __('message.training_notification');
        $notificationData['type'] = 'asset';
        $notificationData['user_id'] = $userIds;
        $notificationData['description'] = $message;
        $notificationData['notification_for_id'] = $data['id'];
        $notification = $this->notificationService->store($notificationData);
        if($notification){
            SMPushHelper::sendTrainingNotification('Training Notification', $message, $userIds);

        }

    }

    private function sendTrainerEmailAndNotification($topic, $trainerIds,$trainingDetail,$message,$key )
    {
        $trainers = $this->trainerService->findTrainers($trainerIds,['id','employee_id','name','contact_number', 'email', 'expertise', 'address']);

        $externalTrainer = $trainers->whereNull('employee_id');
        $internalTrainerIds = $trainers->whereNotNull('employee_id')->pluck('employee_id');

        foreach($externalTrainer as $trainer){
            $company = $this->companyRepository->getCompanyDetail();
            $mailData = [
                'topic'=> ucfirst($topic),
                'company'=>$company,
                'training' => $trainingDetail,
                'trainer' => $trainer
            ];


            $template = match($key) {
                'change' => 'admin.trainingManagement.training.mail.update_mail',
                'remove' => 'admin.trainingManagement.training.mail.removal_mail',
                default => 'admin.trainingManagement.training.mail.invitation_mail'
            };

            // Render the blade template with data
            $htmlContent = View::make($template, $mailData)->render();

            // Email headers
            $headers = [
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'From: '.$company->name.' <noreply@cnattendancev2.cyclonenepal.com>',
                'Reply-To: "Support" <support@cnattendancev2.cyclonenepal.com>',
                'Return-Path: noreply@cnattendancev2.cyclonenepal.com',
                'X-Mailer: PHP/' . phpversion()
            ];
            $headerString = implode("\r\n", $headers);


            mail(
                $trainer->email,
                "Training " . ucfirst($key) . " Notification",
                $htmlContent,
                $headerString
            );

        }


        $this->sendNoticeNotification( $message,$trainingDetail,$internalTrainerIds->toArray());
    }


    /**
     * @param $branchId
     * @return JsonResponse
     */
    public function getBranchTrainingData($branchId)
    {
        try {

            $types = $this->trainingTypeService->getAllActiveBranchTrainingTypes($branchId, ['id','title']);
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
