<?php

namespace App\Http\Controllers\Web;

use App\Enum\LoanInterestTypeEnum;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Requests\LoanManagement\LoanTypeRequest;
use App\Services\LoanManagement\LoanTypeService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanTypeController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.loanManagement.types.';

    public function __construct(
        protected LoanTypeService $LoanTypeService, protected BranchRepository $branchRepository,  protected CompanyRepository $companyRepository
    ){}

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_type');
        try{

            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'type' => $request->type ?? null,
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $select = ['*'];
            $LoanTypeLists = $this->LoanTypeService->getAllLoanTypes($filterParameters,$select);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);

            $interestTypes = LoanInterestTypeEnum::cases();
            return view($this->view.'index', compact('LoanTypeLists','filterParameters','companyDetail','interestTypes'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(LoanTypeRequest $request)
    {
        $this->authorize('create_type');
        try{
            $validatedData = $request->validated();
            $this->LoanTypeService->store($validatedData);
            return response()->json(['message' => __('message.add_award_type')]);
        }catch(\Exception $exception){
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('show_type');
        try{
            $select = ['*'];
            $with = ['branch:id,name'];
            $LoanTypeDetail = $this->LoanTypeService->findLoanTypeById($id,$select,$with);
            return response()->json([
                'LoanTypeDetail' => $LoanTypeDetail,
            ]);
        }catch(\Exception $exception){
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $this->authorize('edit_type');
        try{
            $LoanTypeDetail = $this->LoanTypeService->findLoanTypeById($id);

            return response()->json([
                'LoanTypeDetail' => $LoanTypeDetail,
            ]);
        }catch(\Exception $exception){
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }

    public function update(LoanTypeRequest $request, $id)
    {
        $this->authorize('edit_type');
        try{
            $validatedData = $request->validated();
            $this->LoanTypeService->updateLoanType($id,$validatedData);
            return response()->json(['message' => __('message.Loan_type_update')]);
        }catch(\Exception $exception){
            DB::rollBack();
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_type');
        try{
            DB::beginTransaction();
            $this->LoanTypeService->deleteLoanType($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.Loan_type_delete'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function toggleIsActiveStatus($id)
    {
        $this->authorize('edit_type');
        try{
            $this->LoanTypeService->toggleIsActiveStatus($id);
            return redirect()->back()->with('success', __('message.status_changed'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }
}
