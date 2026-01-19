<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use App\Requests\AssetManagement\AssetDetailRequest;
use App\Services\AssetManagement\AssetService;
use App\Services\AssetManagement\AssetTypeService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.assetManagement.assetDetail.';

    public function __construct(
        protected AssetService     $assetService,
        protected AssetTypeService $assetTypeService,
        protected UserRepository   $userRepo,
        protected CompanyRepository $companyRepository
    )
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_assets');
        try {
            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'name' => $request->name ?? null,
                'purchased_from' => $request->purchased_from ?? null,
                'purchased_to' => $request->purchased_to ?? null,
                'is_working' => $request->is_working ?? null,
                'is_available' => $request->is_available ?? null,
                'type_id' => $request->type_id ?? null,
            ];
            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $select = ['*'];
            $with = ['type:id,name','latestAssignment.user:id,name','branch:id,name'];
            $assetLists = $this->assetService->getAllAssetsPaginated($filterParameters,$select,$with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'index', compact('assetLists','companyDetail','filterParameters'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create_assets');
        try {
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'create',compact('companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function store(AssetDetailRequest $request)
    {
        $this->authorize('create_assets');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->assetService->saveAssetDetail($validatedData);
            DB::commit();
            return redirect()->route('admin.assets.index')->with('success', __('message.asset_saved'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('show_asset');
        try {
            $select = ['*'];
            $with = ['type:id,name'];
            $assetDetail = $this->assetService->findAssetById($id,$select,$with,);


            $assetDetail->name = ucfirst($assetDetail->name);
            $assetDetail->asset_code = $assetDetail->asset_code ?? '';
            $assetDetail->asset_serial_no = $assetDetail->asset_serial_no ?? '';
            $assetDetail->is_working = ucfirst($assetDetail->is_working);
            $assetDetail->assetType = $assetDetail->type->name;
            $assetDetail->image = $assetDetail->image ? asset(Asset::UPLOAD_PATH.$assetDetail->image):'';
            $assetDetail->purchased_date = isset($assetDetail->purchased_date) ? AppHelper::formatDateForView($assetDetail->purchased_date):'';
            $assetDetail->warranty_available = $assetDetail->warranty_available ?? '';
            $assetDetail->is_available = $assetDetail->is_available == 1 ? __('index.yes') : __('index.no');
            $assetDetail->note = removeHtmlTags($assetDetail->note);
            $assetDetail->used_for = AppHelper::getAssetUsedDays($id);

            return response()->json([
                'data' => $assetDetail,
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit($id)
    {
        $this->authorize('edit_assets');
        try {
            $assetDetail = $this->assetService->findAssetById($id);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'edit', compact('assetDetail','companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function update(AssetDetailRequest $request, $id)
    {
        $this->authorize('edit_assets');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->assetService->updateAssetDetail($id, $validatedData);
            DB::commit();
            return redirect()->route('admin.assets.index')
                ->with('success', __('message.asset_update'));
        } catch (Exception $exception) {
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
        $this->authorize('delete_assets');
        try {
            DB::beginTransaction();
                $this->assetService->deleteAsset($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.asset_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }






    /**
     * @param $branchId
     * @return JsonResponse
     */
    public function getBranchAssetData($branchId)
    {
        try {

            $types = $this->assetTypeService->getBranchAssetTypes($branchId, ['id','name']);

            return response()->json([
                'types' => $types,
            ]);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }

    }





}
