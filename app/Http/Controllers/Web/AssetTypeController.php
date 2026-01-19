<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Requests\AssetManagement\AssetTypeRequest;
use App\Services\AssetManagement\AssetTypeService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetTypeController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.assetManagement.types.';

    public function __construct(
        protected AssetTypeService $assetTypeService, protected BranchRepository $branchRepository,  protected CompanyRepository $companyRepository
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
            $with = ['assets'];
            $assetTypeLists = $this->assetTypeService->getAllAssetTypes($filterParameters,$select,$with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view.'index', compact('assetTypeLists','filterParameters','companyDetail'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


//    public function create()
//    {
//        $this->authorize('create_type');
//        try{
//            $companyId = AppHelper::getAuthUserCompanyId();
//            $selectBranch = ['id','name'];
//            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);
//            return view($this->view.'create',compact('branch'));
//        }catch(\Exception $exception){
//            return redirect()->back()->with('danger', $exception->getMessage());
//        }
//    }


    public function store(AssetTypeRequest $request)
    {
        $this->authorize('create_type');
        try{
            $validatedData = $request->validated();
            $this->assetTypeService->store($validatedData);
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
            $with = ['assets:id,type_id,name,purchased_date,is_working,is_available'];
            $assetTypeDetail = $this->assetTypeService->findAssetTypeById($id,$select,$with);
            return view($this->view.'show', compact('assetTypeDetail'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function edit($id)
    {
        $this->authorize('edit_type');
        try{
            $assetTypeDetail = $this->assetTypeService->findAssetTypeById($id);

            return response()->json([
                'assetTypeDetail' => $assetTypeDetail,
            ]);
        }catch(\Exception $exception){
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }


    public function update(AssetTypeRequest $request, $id)
    {
        $this->authorize('edit_type');
        try{
            $validatedData = $request->validated();
            $this->assetTypeService->updateAssetType($id,$validatedData);
            return response()->json(['message' => __('message.asset_type_update')]);
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
                $this->assetTypeService->deleteAssetType($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.asset_type_delete'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function toggleIsActiveStatus($id)
    {
        $this->authorize('edit_type');
        try{
            $this->assetTypeService->toggleIsActiveStatus($id);
            return redirect()->back()->with('success', __('message.status_changed'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }
}
