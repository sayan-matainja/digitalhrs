<?php

namespace App\Repositories;

use App\Models\PermissionGroup;
use App\Models\PermissionGroupType;
use App\Models\PermissionRole;
use App\Models\Role;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleRepository
{
    const IS_ACTIVE = 1;

    public function getAllUserRoles($select=['*'])
    {
        return Role::select($select)->latest()->get();
    }

    public function getAllRolesExceptAdmin($select=['*'])
    {
        return Role::select($select)->where('slug','!=','admin')->get();
    }

    public function getAllActiveRoles($select=['*'])
    {
        return Role::select($select)->where('is_active',1)->get();
    }

    public function getAllActiveRolesByPermission($permissionKey)
    {
        return Role::select('roles.id','roles.name')->leftJoin('permission_roles', 'roles.id', 'permission_roles.role_id')
            ->leftJoin('permissions','permission_roles.permission_id','permissions.id')
            ->where('permissions.permission_key',$permissionKey)->where('roles.is_active',1)->get();
    }

    public function store($validatedData)
    {
        $validatedData['created_by'] = getAuthUserCode() ?? null;
        $validatedData['slug'] =   Str::slug($validatedData['name']);
        return Role::create($validatedData)->fresh();
    }

    public function  getRoleById($id,$select=['*'],$with=[])
    {
        return Role::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function delete($roleDetail)
    {
        return $roleDetail->delete();
    }

    public function update($roleDetail,$validatedData)
    {
        $validatedData['slug'] =  Str::slug($validatedData['name']);
        return $roleDetail->update($validatedData);
    }

    public function toggleStatus($id)
    {
        $roleDetail = Role::where('id',$id)->first();
        if ($roleDetail->slug == 'admin') {
            throw new Exception('Sorry, admin role status cannot be changed.', 403);
        }
        return $roleDetail->update([
            'is_active' => !$roleDetail->is_active,
        ]);
    }

    public function getPermissionGroupDetail($select=['*'],$with=[])
    {
        return  PermissionGroup::select($select)
            ->with($with)
            ->get();
    }

    public function getPermissionGroupTypeDetails($select=['*'],$with=[])
    {
        return PermissionGroupType::select($select)
            ->with($with)
            ->get();
    }

    public function syncPermissionToRole($roleDetail,$permissions)
    {
        return $roleDetail->permission()->sync($permissions);
    }


}
