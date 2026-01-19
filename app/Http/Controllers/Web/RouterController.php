<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Repositories\CompanyRepository;
use App\Repositories\RouterRepository;
use App\Requests\Router\RouterRequest;

use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RouterController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.router.';

    public function __construct(
        public RouterRepository $routerRepo,
        public CompanyRepository $companyRepo
    )
    {
    }

    public function index(Request $request)
    {
        $this->authorize('list_router');
        try {
            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $with = ['branch:id,name', 'company:id,name'];
            $select = ['id', 'branch_id', 'company_id', 'router_ssid', 'is_active'];
            $routers = $this->routerRepo->getAllRouters($filterParameters,$select, $with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            return view($this->view . 'index', compact('routers','filterParameters','companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create_router');
        try {
            $with = ['branches:company_id,id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            return view($this->view . 'create', compact('companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(RouterRequest $request)
    {
        $this->authorize('create_router');
        try {
            $validatedData = $request->validated();
            $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
            $validatedData['is_active'] = 1;
            DB::beginTransaction();
            $this->routerRepo->store($validatedData);
            DB::commit();
            return redirect()->route('admin.routers.index')
                ->with('success', __('message.router_add'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function show(Router $router)
    {
        //
    }

    public function edit($id)
    {
        $this->authorize('edit_router');
        try {
            $with = ['branches:company_id,id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            $routerDetail = $this->routerRepo->findRouterDetailById($id);
            return view($this->view . 'edit', compact('routerDetail', 'companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(RouterRequest $request, $id)
    {
        $this->authorize('edit_router');
        try {
            $validatedData = $request->validated();
            $routerDetail = $this->routerRepo->findRouterDetailById($id);
            if (!$routerDetail) {
                throw new Exception(__('message.router_detail_not_found'), 404);
            }
            DB::beginTransaction();
            $this->routerRepo->update($routerDetail, $validatedData);
            DB::commit();
            return redirect()
                ->route('admin.routers.index')
                ->with('success', __('message.router_update'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }

    }

    public function toggleStatus($id)
    {
        $this->authorize('edit_router');
        try {
            DB::beginTransaction();
            $this->routerRepo->toggleStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_router');
        try {
            $routerDetail = $this->routerRepo->findRouterDetailById($id);
            if (!$routerDetail) {
                throw new Exception(__('message.router_detail_not_found'), 404);
            }
            DB::beginTransaction();
            $this->routerRepo->delete($routerDetail);
            DB::commit();
            return redirect()->back()->with('success', __('message.router_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
}
