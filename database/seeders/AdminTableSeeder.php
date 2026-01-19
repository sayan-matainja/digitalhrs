<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $user = User::where('id',1)->first();

        if($user){
            $userImagePath = User::AVATAR_UPLOAD_PATH;
            $adminImagePath = Admin::AVATAR_UPLOAD_PATH;
            $avatarFilename = $user->avatar;


            Admin::create([
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'password' => $user->password,
                'avatar' => $avatarFilename,
                'is_active' => $user->is_active,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if ($avatarFilename && File::exists(public_path($userImagePath . $avatarFilename))) {
                if (!File::exists(public_path($adminImagePath))) {
                    File::makeDirectory(public_path($adminImagePath), 0755, true);
                }
                File::move(
                    public_path($userImagePath . $avatarFilename),
                    public_path($adminImagePath . $avatarFilename)
                );

            }
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $role = Role::where('id', 1)->first();
            if ($role) {
                $user->delete();
                $role->delete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }else{
            Admin::create([
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'username' => 'admin123',
                'password' => bcrypt('admin123'),
                'avatar' => null,
                'is_active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }


    }
}
