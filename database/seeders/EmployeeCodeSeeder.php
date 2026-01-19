<?php

namespace Database\Seeders;

use App\Helpers\AppHelper;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class EmployeeCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $existingRows = DB::table('users')->where('role_id','!=',1)->orderBy('created_at')->get();

        $prefix = AppHelper::getEmployeeCodePrefix();
        $i = 1;
        foreach ($existingRows as $row) {
            $code = $prefix.'-'.str_pad($i, 5, '0', STR_PAD_LEFT);
            DB::table('users')
                ->where('id', $row->id)
                ->update(['employee_code' => $code]);

            $i++;
        }
    }
}
