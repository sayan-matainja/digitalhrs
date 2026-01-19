<?php

namespace Database\Seeders;

use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeAccountSeeder extends Seeder
{
    public function run()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            if (Schema::hasTable('employee_accounts')) {
                DB::table('employee_accounts')->truncate();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $users = DB::table('users')->select(
                'id',
                'name',
            )->get();

            $accountDetail = $users->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'bank_name' => 'ABC Bank',
                    'bank_account_no' => '067800054570',
                    'bank_account_type' => 'salary',
                    'account_holder' => $user->name,
                ];
            })->toArray();

            DB::table('employee_accounts')->insert($accountDetail);

        } catch (Exception $e) {
            dump('~EmployeeAccountDetail', $e->getMessage());
        }
    }
}
