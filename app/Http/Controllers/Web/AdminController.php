<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Requests\Admin\AdminRequest;
use App\Requests\User\ChangePasswordRequest;
use App\Requests\User\UserCreateRequest;
use App\Requests\User\UserUpdateRequest;
use App\Services\Admin\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class AdminController extends Controller
{
    private $view = 'admin.users.';


    public function __construct(protected AdminService $adminService,)
    {
    }

    public function index(Request $request)
    {
        try {

            $admins = $this->adminService->getAllAdmin();
            return view($this->view . 'index', compact('admins'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        try {
            return view($this->view . 'create');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(AdminRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $validatedData['password'] = bcrypt($validatedData['password']);
            $validatedData['is_active'] = 1;


            DB::beginTransaction();
                $this->adminService->saveAdmin($validatedData);
            DB::commit();
            return redirect()
                ->route('admin.users.index')
                ->with('success', __('message.add_user'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        try {
            $userDetail = $this->adminService->findAdminById($id);
            return view($this->view . 'show2', compact('userDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getFile());
        }
    }

    public function edit($id)
    {

        try {

            $userDetail = $this->adminService->findAdminById($id);

            return view($this->view . 'edit', compact( 'userDetail'));
        } catch (Exception $exception) {

            return redirect()->back()->with('danger', $exception->getFile());
        }
    }

    public function update(AdminRequest $request,$id)
    {
        try {

            $validatedData = $request->validated();

            if (env('DEMO_MODE', false) && (in_array($id, [1, 2]))) {
                throw new Exception(__('message.add_company_warning'), 400);
            }



            DB::beginTransaction();
            $this->adminService->updateAdmin($id, $validatedData);

            DB::commit();
            return redirect()
                ->route('admin.users.index')
                ->with('success', __('message.update_user'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            DB::beginTransaction();
            $this->adminService->toggleIsActiveStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.user_is_active_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        try {

            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            $adminDetail = $this->adminService->findAdminById($id);

            if (!$adminDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }

            if ($adminDetail->id == auth('admin')->user()->id) {
                throw new Exception(__('message._delete_own'), 402);
            }

            DB::beginTransaction();
            $this->adminService->deleteAdmin($adminDetail);
            DB::commit();
            return redirect()->back()->with('success', __('message.user_remove'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }




    public function changePassword(ChangePasswordRequest $request, $userId)
    {

        try {
            $validatedData = $request->validated();
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            $validatedData['new_password'] = bcrypt($validatedData['new_password']);
            DB::beginTransaction();
            $this->adminService->updateAdmin($userId, ['password'=>$validatedData['new_password']]);
            DB::commit();
            return redirect()->back()->with('success', __('message.user_password_change'));

        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}
