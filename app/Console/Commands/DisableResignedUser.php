<?php

namespace App\Console\Commands;

use App\Enum\ResignationStatusEnum;
use App\Models\Resignation;
use App\Models\User;
use Illuminate\Console\Command;

class DisableResignedUser extends Command
{
    const IS_ACTIVE = 1;

    protected $signature = 'command:disable-user';

    protected $description = 'Disable resigned employees of company.';

    public function handle()
    {
        $todayDate = now()->format('Y-m-d');
        $resignationData = Resignation::query()
            ->where('status', ResignationStatusEnum::approved->value)
            ->with('employee')
            ->get();

        if ($resignationData->isNotEmpty()) {

            foreach ($resignationData as $resignation) {

                $userDetail = $resignation->employee;

                if ($userDetail) {
                    $lastWorkingDay = $resignation->last_working_day;
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
                                'status' => 'resigned',
                            ]);
                        }
                    }

                }

            }

        }
        $this->info('Resigned employee account deactivated!');
    }
}
