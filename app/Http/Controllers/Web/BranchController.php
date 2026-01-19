<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use App\Requests\Branch\BranchRequest;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.branch.';

    private BranchRepository $branchRepo;
    private CompanyRepository $companyRepo;
    private UserRepository $userRepo;

    public function __construct(BranchRepository $branchRepo,
                                CompanyRepository $companyRepo,
                                UserRepository $userRepo
    )
    {
        $this->branchRepo = $branchRepo;
        $this->companyRepo = $companyRepo;
        $this->userRepo = $userRepo;
    }

    public function index(Request $request)
    {
        $this->authorize('list_branch');
        try{
            $select = ['*'];
            $filterParameters = [
                'name' =>  $request->name ?? null,
                'per_page' => $request->per_page ?? Branch::RECORDS_PER_PAGE,
            ];
            $branches = $this->branchRepo->getAllCompanyBranches($filterParameters,$select);
            return view($this->view.'index', compact('branches','filterParameters'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create_branch');
        try{
            $select=['name','id'];
            $users = $this->userRepo->getAllVerifiedEmployeeOfCompany($select);
            $company = $this->companyRepo->getCompanyDetail(['id','name']);

            return view($this->view.'create', compact('users','company'));
        }catch(\Exception $exception){
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }

    public function store(BranchRequest $request)
    {
        $this->authorize('create_branch');
        try{
            $validatedData = $request->validated();
            if(isset($validatedData['branch_head_id'])){
                $this->checkBranchHead($validatedData['branch_head_id']);
            }


            DB::beginTransaction();
                $this->branchRepo->store($validatedData);
            DB::commit();
            return redirect()
                ->route('admin.branch.index')
                ->with('success', __('message.add_branch'));
        }catch(\Exception $e){
            DB::rollBack();
            return redirect()
                ->route('admin.branch.index')
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $this->authorize('edit_branch');
        try{
            $branch = $this->branchRepo->findBranchDetailById($id);
            $select=['name','id'];
            $users = $this->userRepo->getAllVerifiedEmployeeOfCompany($select);
            $company = $this->companyRepo->getCompanyDetail(['id','name']);
            return view($this->view.'edit', compact('branch','users','company'));
        }catch(\Exception $exception){
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }

    public function update(BranchRequest $request,$id)
    {
        $this->authorize('edit_branch');
        try{
            $validatedData = $request->validated();
            $branchDetail = $this->branchRepo->findBranchDetailById($id);
            if(!$branchDetail){
                throw new \Exception(__('message.branch_not_found'),404);
            }

            if(isset($validatedData['branch_head_id'])){
                $this->checkBranchHead($validatedData['branch_head_id'], $id);
            }



            DB::beginTransaction();
              $this->branchRepo->update($branchDetail,$validatedData);
            DB::commit();
            return redirect()->route('admin.branch.index')->with('success', __('message.update_branch'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit_branch');
        try{
            DB::beginTransaction();
                $this->branchRepo->toggleStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_branch');
        try{
            $with = ['departments','routers'];
            $branchDetail = $this->branchRepo->findBranchDetailById($id,$with);
            if(!$branchDetail){
                throw new \Exception(__('message.branch_not_found'),404);
            }
            if(count($branchDetail->departments) > 0){
                throw new \Exception(__('message.branch_delete_warning'),403);
            }
            if(count($branchDetail->routers) > 0){
                throw new \Exception(__('message.branch_delete_router_warning'),403);
            }
            DB::beginTransaction();
                $this->branchRepo->delete($branchDetail);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_branch'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function checkBranchHead($userId, $branchId=0)
    {
        $statusCheck =  $this->branchRepo->checkBranchHead($userId, $branchId);

        if($statusCheck){
            throw new Exception(__('index.branch_head_error'),404);
        }
    }

}
