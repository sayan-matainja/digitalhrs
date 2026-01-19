<?php

namespace App\Http\Controllers\Web;

use App\Enum\TrainerTypeEnum;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use App\Requests\TrainingManagement\TrainerRequest;
use App\Services\TrainingManagement\TrainerService;
use App\Services\TrainingManagement\TrainingService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrainerController extends Controller
{
    use CustomAuthorizesRequests;
    private string $view = 'admin.trainingManagement.trainer.';

    public function __construct(
        protected TrainerService $trainerService,
        protected UserRepository $userRepository,
        protected BranchRepository $branchRepository,
        protected TrainingService $trainingService,
        protected CompanyRepository $companyRepository
    ){}

    /**
     * Display a listing of the resource.
     *
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_trainer');
        try{

            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'trainer_type' => $request->trainer_type ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'name' => $request->name ?? null,
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }
            $select = ['*'];
            $with = ['employee:id,name,email,phone'];
            $trainerLists = $this->trainerService->getAllTrainerPaginated($filterParameters, $select,$with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            $trainerTypes = TrainerTypeEnum::cases();
            return view($this->view.'index', compact('trainerLists','filterParameters','companyDetail','trainerTypes'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create_trainer');

        try{
            $companyId = AppHelper::getAuthUserCompanyId();
            $selectBranch = ['id','name'];
            $trainerTypes = TrainerTypeEnum::cases();
            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);
            return view($this->view.'create', compact('trainerTypes','branch'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @throws AuthorizationException
     */
    public function store(TrainerRequest $request)
    {
        $this->authorize('create_trainer');

        try{
            $validatedData = $request->validated();
            $this->trainerService->saveTrainerDetail($validatedData);
            return redirect()->route('admin.trainers.index')->with('success',__('message.add_trainer') );
        }catch(Exception $exception){
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
        $this->authorize('show_trainer');

        try{
            $select = ['*'];
            $with = ['employee:id,name,email,phone,address','branch:id,name','department:id,dept_name'];
            $trainerDetail = $this->trainerService->findTrainerById($id,$select,$with);

            $trainerDetail->type = ucfirst($trainerDetail->trainer_type);

            if($trainerDetail->trainer_type == \App\Enum\TrainerTypeEnum::internal->value){
                $trainerDetail->branchName = $trainerDetail->branch?->name;
                $trainerDetail->departmentName = $trainerDetail?->department?->dept_name;
                $trainerDetail->name = $trainerDetail->employee?->name;
                $trainerDetail->email = $trainerDetail->employee?->email;
                $trainerDetail->phone = $trainerDetail->employee?->phone;
                $trainerDetail->address = $trainerDetail->employee?->address;
            }else{
                $trainerDetail->branchName = $trainerDetail->branch?->name;
                $trainerDetail->phone = $trainerDetail->contact_number;
                $trainerDetail->expertise = ucfirst($trainerDetail->expertise);
            }

            return response()->json([
                'data' => $trainerDetail,
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
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
        $this->authorize('update_trainer');
        try{
            $trainerDetail = $this->trainerService->findTrainerById($id);
            $companyId = AppHelper::getAuthUserCompanyId();
            $selectBranch = ['id','name'];
            $trainerTypes = TrainerTypeEnum::cases();
            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);
            return view($this->view.'edit', compact('trainerDetail','trainerTypes','branch'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws AuthorizationException
     */
    public function update(TrainerRequest $request, $id)
    {
        $this->authorize('update_trainer');
        try{
            $validatedData = $request->validated();
            $this->trainerService->updateTrainerDetail($id,$validatedData);
            return redirect()->route('admin.trainers.index')
                ->with('success', __('message.update_trainer'));
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
        $this->authorize('delete_trainer');
        try{

            $checkTrainer = $this->trainingService->checkTrainer($id);
            if ($checkTrainer) {
                return redirect()->back()->with('danger',  __('message.trainer_delete_error'));
            }
            DB::beginTransaction();
            $this->trainerService->deleteTrainer($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_trainer'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function toggleStatus($id)
    {
        $this->authorize('update_trainer');
        try{

            $checkTrainer = $this->trainingService->checkTrainer($id);
            if ($checkTrainer) {
                return redirect()->back()->with('danger',  __('message.trainer_status_change_error'));
            }

            $this->trainerService->toggleStatus($id);
            return redirect()->back()->with('success', __('message.status_changed'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function getAllTrainersByType($type): JsonResponse|RedirectResponse
    {
        try {

            $trainers = $this->trainerService->getTrainerByType($type);
            return response()->json([
                'data' => $trainers
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }
}
