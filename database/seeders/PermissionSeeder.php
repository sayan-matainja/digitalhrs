<?php

namespace Database\Seeders;


use App\Helpers\RolePermissionHelper;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('permissions')->truncate();
            DB::table('permission_groups')->truncate();
            DB::table('permission_group_types')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $permissionGroupTypeArray = RolePermissionHelper::permissionModuleTypeArray();
            $permissionArray = RolePermissionHelper::permissionArray();
            $permission_module_array = RolePermissionHelper::permissionModuleArray();

            DB::table('permission_group_types')->insert($permissionGroupTypeArray);
            DB::table('permission_groups')->insert($permission_module_array);
            DB::table('permissions')->insert($permissionArray);

        } catch (Exception $e) {
            dump('~Permission', $e->getMessage());
        }
    }
}
