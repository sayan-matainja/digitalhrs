<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Repositories\ContentManagementRepository;
use App\Requests\ContentManagement\ContentManagementRequest;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaticPageContentController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.contentManagement.';

    private ContentManagementRepository $contentMgmtRepo;
    private CompanyRepository $companyRepo;


    public function __construct(ContentManagementRepository $contentMgmtRepo,CompanyRepository $companyRepo)
    {
        $this->contentMgmtRepo = $contentMgmtRepo;
        $this->companyRepo = $companyRepo;
    }

    public function index()
    {
        $this->authorize('list_content');
        try{
            $staticPageContents = $this->contentMgmtRepo->getAllCompanyContentManagementDetail();
            return view($this->view.'index', compact('staticPageContents'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create_content');
        try{
            $select = ['id','name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select);
            return view($this->view.'create',
                compact('companyDetail')
            );
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $this->authorize('show_content');
            $select = ['description','title'];
            $contentDescription = $this->contentMgmtRepo->findCompanyContentById($id,$select);
            $contentDescription->description = removeHtmlTags($contentDescription->description);
            return response()->json([
                'data' => $contentDescription,
            ]);
        } catch (\Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }

    public function store(ContentManagementRequest $request)
    {
        $this->authorize('create_content');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $validatedData['title_slug'] = Str::slug($validatedData['title']);
            $this->contentMgmtRepo->store($validatedData);
            DB::commit();
            return redirect()->back()->with('success', __('message.add_page'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }


    public function edit($id)
    {
        $this->authorize('edit_content');
        try{
            $companyContentDetail = $this->contentMgmtRepo->findCompanyContentById($id);
            $select = ['id','name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select);
            return view($this->view.'edit', compact('companyContentDetail','companyDetail'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function update(ContentManagementRequest $request, $id)
    {
        $this->authorize('edit_content');
        try{
            $validatedData = $request->validated();
            $companyContentDetail = $this->contentMgmtRepo->findCompanyContentById($id);
            if(!$companyContentDetail){
                throw new \Exception('Office Time Detail Not Found',404);
            }
            DB::beginTransaction();
            $validatedData['title_slug'] = Str::slug($validatedData['title']);
            $this->contentMgmtRepo->update($companyContentDetail,$validatedData);
            DB::commit();
            return redirect()->back()->with('success',  __('message.update_page'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit_content');
        try {
            DB::beginTransaction();
            $this->contentMgmtRepo->toggleStatus($id);
            DB::commit();
            return redirect()->back()->with('success',  __('message.change_page_status'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_content');
        try {
            $companyContentDetail = $this->contentMgmtRepo->findCompanyContentById($id);
            if (!$companyContentDetail) {
                throw new \Exception( __('message.static_page_not_found'), 404);
            }
            DB::beginTransaction();
            $this->contentMgmtRepo->delete($companyContentDetail);
            DB::commit();
            return redirect()->back()->with('success',  __('message.delete_page'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}
