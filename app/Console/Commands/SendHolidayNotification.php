<?php

namespace App\Console\Commands;

use App\Helpers\SMPush\SMPushHelper;
use App\Models\Holiday;
use App\Models\User;
use Illuminate\Console\Command;

class SendHolidayNotification extends Command
{
    const IS_ACTIVE = 1;

    protected $signature = 'command:holiday-notification';

    protected $description = 'Send holiday notification to all company employees';

    public function handle()
    {
        $todayDate = now()->format('Y-m-d');
        $holidayDetail = Holiday::query()
            ->whereDate('event_date', $todayDate)
            ->where('is_active',self::IS_ACTIVE)
            ->first();

        if($holidayDetail){
            $notification = [
                'title' => 'Holiday Notification',
                'description' => ucfirst($holidayDetail->event)
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
        $this->info('Holiday Notification Sent successfully!');
    }
}
