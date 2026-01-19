<?php

namespace App\Http\Controllers\Web;

use App\Enum\AwardBaseEnum;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\AwardManagement\AwardRequest;
use App\Services\AwardManagement\AwardService;
use App\Services\AwardManagement\AwardTypeService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AwardController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.awardManagement.awardDetail.';

    public function __construct(
        protected AwardService $awardService, protected AwardTypeService $awardTypeService,
        protected UserRepository $userRepository, protected DepartmentRepository $departmentRepository, protected CompanyRepository $companyRepository
    ){}

    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $this->authorize('award_list');
        try{

            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'department_id' => $request->department_id ?? null,
                'awarded_date' => $request->awarded_date ?? null,
                'award_type_id' => $request->award_type_id ?? null,
            ];
            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $select = ['*'];
            $with = ['type:id,title','employee:id,name'];
            $awardLists = $this->awardService->getAllAwardPaginated($filterParameters,$select,$with);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view.'index', compact('awardLists','companyDetail','filterParameters'));
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
        $this->authorize('create_award');

        try{
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            $awardBases = AwardBaseEnum::cases();
            $rewardCode = rand(1000, 9999);
            return view($this->view.'create', compact('companyDetail','awardBases','rewardCode'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     */
    public function store(AwardRequest $request)
    {
        $this->authorize('create_award');

        try{
            $validatedData = $request->validated();
            $this->awardService->saveAwardDetail($validatedData);
            return redirect()->route('admin.awards.index')->with('success',__('message.add_award') );
        }catch(Exception $exception){
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
        $this->authorize('show_award');

        try{
            $select = ['*'];
            $with = ['type:id,title','employee:id,name','branch:id,name','department:id,dept_name'];
            $awardDetail = $this->awardService->findAwardById($id,$select,$with);

            return view($this->view.'show', compact('awardDetail'));
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
        $this->authorize('update_award');
        try{
            $awardDetail = $this->awardService->findAwardById($id);
            $awardBases = AwardBaseEnum::cases();

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            // Fetch users by selected departments
            $filteredDepartment = isset($awardDetail->branch_id)
                ? $this->departmentRepository->getAllActiveDepartmentsByBranchId($awardDetail->branch_id, [], ['id', 'dept_name'])
                : [];

            $selectUser = ['name', 'id'];
            $filteredUsers = isset($awardDetail->department_id)
                ? $this->userRepository->getActiveEmployeeOfDepartment($awardDetail->department_id, $selectUser)
                : [];

            $selectType = ['title', 'id'];
            $filteredTypes = isset($awardDetail->branch_id)
                ? $this->awardTypeService->getAllActiveBranchAwardType($awardDetail->branch_id, $selectType)
                : [];

            return view($this->view.'edit', compact('awardDetail','companyDetail','filteredTypes','filteredDepartment','filteredUsers','awardBases'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(AwardRequest $request, $id)
    {
        $this->authorize('update_award');
        try{
            $validatedData = $request->validated();
            $this->awardService->updateAwardDetail($id,$validatedData);
            return redirect()->route('admin.awards.index')
                ->with('success', __('message.update_award'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_award');
        try{
            DB::beginTransaction();
            $this->awardService->deleteAward($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_award'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    /**
     * @param $branchId
     * @return JsonResponse
     */
    public function getBranchAwardData($branchId)
    {
        try {

            $types = $this->awardTypeService->getAllActiveBranchAwardType($branchId, ['id','title']);
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
