<?php

namespace Database\Seeders;

use App\Models\LeaveRequestMaster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateLeaveDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::transaction(function () {
            $leaves = LeaveRequestMaster::with(['leaveRequestedBy:id,department_id'])->whereNull('department_id')->get();

            foreach($leaves as  $leave) {

                if(isset($leave->leaveRequestedBy->department_id)){
                    $leave->update(['department_id' => $leave->leaveRequestedBy->department_id]);
                }
            }
        });

    }
}
