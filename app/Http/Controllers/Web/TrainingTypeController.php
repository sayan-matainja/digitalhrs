<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Requests\TrainingManagement\TrainingTypeRequest;
use App\Services\TrainingManagement\TrainingService;
use App\Services\TrainingManagement\TrainingTypeService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrainingTypeController extends Controller
{

    use CustomAuthorizesRequests;
    private $view = 'admin.trainingManagement.types.';

    public function __construct(
        protected TrainingTypeService $trainingTypeService, protected TrainingService $trainingService,
        protected BranchRepository $branchRepository,
        protected CompanyRepository $companyRepository
    ){}

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|RedirectResponse|Response
     */
    public function index(Request $request)
    {
        $this->authorize('training_type_list');
        try{
            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'type' => $request->type ?? null,
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $select = ['*'];
            $with = ['trainings'];
            $companyId = AppHelper::getAuthUserCompanyId();
            $trainingTypes = $this->trainingTypeService->getAllTrainingTypes($filterParameters,$select,$with);
            $selectBranch = ['id','name'];
            $branches = $this->branchRepository->getLoggedInUserCompanyBranches( $companyId,$selectBranch);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view.'index', compact('trainingTypes','branches','filterParameters','companyDetail'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param TrainingTypeRequest $request
     * @return RedirectResponse|Response
     * @throws AuthorizationException
     */
    public function store(TrainingTypeRequest $request)
    {
        $this->authorize('create_training_type');
        try{

            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->trainingTypeService->store($validatedData);
            DB::commit();
            return redirect()->route('admin.training-types.index')->with('success', __('message.add_training_type'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|RedirectResponse|Response
     */
    public function show($id): View|Factory|Response|RedirectResponse|Application
    {
        $this->authorize('show_training_type');
        try{
            $select = ['*'];
            $with = ['trainings'];
            $trainingType = $this->trainingTypeService->findTrainingTypeById($id,$select,$with);

            return view($this->view.'show', compact('trainingType'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param TrainingTypeRequest $request
     * @param int $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(TrainingTypeRequest $request, $id)
    {
        $this->authorize('update_training_type');
        try{

            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->trainingTypeService->updateTrainingType($id,$validatedData);
            DB::commit();
            return redirect()->route('admin.training-types.index')
                ->with('success', __('message.update_training_type'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_training_type');
        try{

            $checkTrainingType = $this->trainingService->checkType($id);
            if ($checkTrainingType) {
                return redirect()->back()->with('danger',  __('message.training_type_delete_error'));
            }
            DB::beginTransaction();
            $this->trainingTypeService->deleteTrainingType($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_training_type'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('update_training_type');
        try{

            $checkTrainingType = $this->trainingService->checkType($id);
             if ($checkTrainingType) {
                 return redirect()->back()->with('danger',  __('message.training_type_status_change_error'));
             }

            $this->trainingTypeService->toggleStatus($id);
            return redirect()->back()->with('success', __('message.status_changed'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }


}
