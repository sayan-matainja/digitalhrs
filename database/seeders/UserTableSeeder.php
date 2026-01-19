<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       User::create([
           'name' => 'Admin',
           'email' => 'admin@admin.com',
           'username' => 'admin123',
           'password' => bcrypt('admin123'),
           'address' => 'kathmandu,Nepal',
           'avatar' => null,
           'dob' => null,
           'gender' => 'male', // male, female
           'phone' => '9811111111',
           'status' => 'verified', // verified , rejected , pending , suspended
           'is_active' => 1,
           'role_id' => 1,
           'leave_allocated' => null,
           'employment_type' => 'contract', // contract,permanent, temporary
           'user_type' => 'field', // field
           'company_id' => 1,
           'branch_id' => null,
           'department_id' => null,
           'post_id' => null,
           'supervisor_id' => null,
           'office_time_id' => null,
           'remarks' => null,
           'created_by' => null,
           'updated_by' => null,
           'deleted_by' => null,
           'deleted_at' => null,
           'created_at' => Carbon::now(),
           'updated_at' => Carbon::now()
       ]);

    }
}
