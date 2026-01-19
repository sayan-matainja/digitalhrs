<?php

namespace App\Console\Commands;

use App\Enum\ResignationStatusEnum;
use App\Enum\TerminationStatusEnum;
use App\Models\Resignation;
use App\Models\Termination;
use App\Models\User;
use Illuminate\Console\Command;

class DisableTerminatedUser extends Command
{
    const IS_ACTIVE = 1;

    protected $signature = 'command:disable-terminated-user';

    protected $description = 'Disable terminated employees of company.';

    public function handle()
    {
        $todayDate = now()->format('Y-m-d');
        $terminationData = Termination::query()
            ->where('status', TerminationStatusEnum::approved->value)
            ->with('employee')
            ->get();

        if (!empty($terminationData)) {

            foreach ($terminationData as $termination) {

                $userDetail = $termination->employee;

                if($userDetail){
                    $lastWorkingDay = $termination->termination_date;
                    $actionDate = \Carbon\Carbon::parse($lastWorkingDay)->addDay()->format('Y-m-d');
                    if ($todayDate == $actionDate) {
                        $userDetail->tokens()->delete();

                        $userDetail->update([
                            'is_active' => false,
                            'logout_status' => true,
                            'fcm_token' => null,
                            'online_status' => false
                        ]);
                    } else {

                        if ($userDetail->status == 'verified') {
                            $userDetail->update([
                                'status' => 'terminated',
                            ]);
                        }
                    }
                }


            }

        }
        $this->info('Terminated employee account deactivated!');
    }
}
