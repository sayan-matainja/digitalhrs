<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PermissionGroup;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Requests\Role\RoleRequest;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class  RoleController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.role.';

    private RoleRepository $roleRepo;
    private UserRepository $userRepo;

    public function __construct(RoleRepository $roleRepo, UserRepository $userRepo)
    {
        $this->roleRepo = $roleRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('role_permission');
        try {
            $roles = $this->roleRepo->getAllUserRoles();
            return view($this->view . 'index', compact('roles'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('role_permission');
        try {
            return view($this->view . 'create');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function store(RoleRequest $request)
    {
        $this->authorize('role_permission');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->roleRepo->store($validatedData);
            DB::commit();
            Artisan::call('cache:clear');
            return redirect()->route('admin.roles.index')->with('success', __('message.add_role'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit($id)
    {
        $this->authorize('role_permission');
        try {
            $roleDetail = $this->roleRepo->getRoleById($id);
            if (!$roleDetail) {
                throw new Exception(__('message.role_not_found'), 204);
            }
            if ($roleDetail->slug == 'admin') {
                throw new Exception(__('message.admin_role_delete_error'), 402);
            }
            return view($this->view . 'edit', compact('roleDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function update(RoleRequest $request, $id)
    {
        $this->authorize('role_permission');
        try {
            $validatedData = $request->validated();
            $roleDetail = $this->roleRepo->getRoleById($id);
            if (!$roleDetail) {
                throw new Exception(__('message.role_not_found'), 404);
            }
            DB::beginTransaction();
            $this->roleRepo->update($roleDetail, $validatedData);
            DB::commit();
            Artisan::call('cache:clear');
            return redirect()->route('admin.roles.index')->with('success', __('message.role_update'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }

    }

    /**
     * @throws AuthorizationException
     */
    public function toggleStatus($id)
    {
        $this->authorize('role_permission');
        try {
            DB::beginTransaction();
                $this->roleRepo->toggleStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function delete($id)
    {
        $this->authorize('role_permission');
        try {
            $roleDetail = $this->roleRepo->getRoleById($id);
            if (!$roleDetail) {
                throw new Exception(__('message.role_not_found'), 404);
            }
            if ($roleDetail->slug == 'admin') {
                throw new Exception(__('message.admin_role_delete_error'), 402);
            }
            $user = $this->userRepo->findUserDetailByRole($id);
            {
                if ($user) {
                    throw new Exception(__('message.assign_role_delete_error'), 402);
                }
            }
            DB::beginTransaction();
            $this->roleRepo->delete($roleDetail);
            DB::commit();
            Artisan::call('cache:clear');
            return redirect()->back()->with('success', __('message.role_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function createPermission($roleId): Factory|View|RedirectResponse|Application
    {
        $this->authorize('role_permission');
        try {
            $selectPermissionGroup = ['*'];
            $selectRole = ['id', 'name', 'slug'];
            $withPermissionType = ['permissionGroups','permissionGroups.getPermission'];
            $withRole = ['permission'];
            $permissionGroupTypeList = $this->roleRepo->getPermissionGroupTypeDetails($selectPermissionGroup, $withPermissionType);
            $role = $this->roleRepo->getRoleById($roleId, $selectRole, $withRole);
            $allRoles = $this->roleRepo->getAllRolesExceptAdmin();
            if (!$role) {
                throw new Exception(__('message.role_not_found'), 404);
            }

            $isEdit = false;
            $role_permission = [];
            if ($role->getRolePermission->count() > 0) {
                $role_permission = $role->getRolePermission->pluck('permission_id')->toArray();
                $isEdit = true;
            }
            return view($this->view . 'permission', compact('permissionGroupTypeList',
                'role',
                'role_permission', 'isEdit','allRoles'
            ));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function assignPermissionToRole(Request $request, $roleId): RedirectResponse
    {
        $this->authorize('role_permission');
        try {
            $data = $request->all();
            $role = $this->roleRepo->getRoleById($roleId);
            $validatedPermissionData = $data['permission_value'] ?? [];
            DB::beginTransaction();
                $this->roleRepo->syncPermissionToRole($role, $validatedPermissionData);
            DB::commit();
            Cache::forget("user_permissions_role_{$roleId}");
            Artisan::call('cache:clear');
            return redirect()->back()->with('success', __('message.permission_update'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}
