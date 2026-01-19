<?php

namespace App\Console\Commands;

use App\Enum\ResignationStatusEnum;
use App\Models\Resignation;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Console\Command;

class UpdateTransferredUser extends Command
{
    const IS_ACTIVE = 1;

    protected $signature = 'command:update-transferred-user';

    protected $description = 'update transferred employees of company.';

    public function handle()
    {
        $todayDate = now()->format('Y-m-d');
        $transferData = Transfer::query()
            ->where('transfer_date', $todayDate)
            ->get();



        if (!empty($transferData)) {

            foreach ($transferData as $transfer) {

                $userDetail = User::where('id', $transfer->employee_id)->first();

                $userDetail->update([
                    'branch_id' => $transfer->branch_id,
                    'department_id' => $transfer->department_id,
                    'post_id' => $transfer->post_id,
                    'supervisor_id' => $transfer->supervisor_id,
                    'office_time_id' => $transfer->office_time_id,
                ]);

            }

        }
        $this->info('Transferred employee account updated!');
    }
}
