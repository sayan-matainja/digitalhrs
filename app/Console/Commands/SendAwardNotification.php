<?php

namespace App\Console\Commands;

use App\Helpers\SMPush\SMPushHelper;
use App\Models\Award;
use App\Models\Holiday;
use App\Models\User;
use Illuminate\Console\Command;

class SendAwardNotification extends Command
{
    const IS_ACTIVE = 1;

    protected $signature = 'command:award-notification';

    protected $description = 'Send award notification to all company employees';

    public function handle()
    {
        $todayDate = now()->format('Y-m-d');
        $awardDetail = Award::query()
            ->with(['employee:id,name','type:id,title'])
            ->whereDate('awarded_date', $todayDate)
            ->get();

        if(!empty($awardDetail)){

            foreach ($awardDetail as $award){
                $notification = [
                    'title' => 'Award Notification',
                    'description' => $award->type->title. ' has been awarded to ' .$award->employee->name
                ];
                $userId = User::query()->where([
                    ['status', '=', 'verified'],
                    ['is_active', '=', self::IS_ACTIVE ],
                ])
                    ->pluck('id')
                    ->toArray();
                if(count($userId) > 0){
                    SMPushHelper::sendPush($notification['title'], $notification['description'],$userId);
                }
            }

        }
        $this->info('Award Notification Sent successfully!');
    }
}
