<?php

namespace App\Console\Commands;

use App\Helpers\SMPush\SMPushHelper;
use App\Models\Holiday;
use App\Models\User;
use Illuminate\Console\Command;

class SendBirthdayNotification extends Command
{
    const IS_ACTIVE = 1;

    protected $signature = 'command:birthday-notification';

    protected $description = 'Send birthday notification to all company employees';

    public function handle()
    {
        $todayDate = now()->format('m-d');
        $birthdayDetail = User::query()
            ->whereRaw("DATE_FORMAT(dob, '%m-%d') = ?", [$todayDate])
            ->where('is_active',self::IS_ACTIVE)
            ->whereNull('deleted_at')
            ->get();


        if(!empty($birthdayDetail)){

            foreach ($birthdayDetail as $birthday){
                $notification = [
                    'title' => "It's ". $birthday->name."'s Birthday today ",
                    'description' => " Send your warmest wishes for ".$birthday->name."'s birthday.🥳🎂"
                ];
                $userId = User::query()->where([
                    ['status', '=', 'verified'],
                    ['is_active', '=', self::IS_ACTIVE ],
                ])
                    ->whereNull('deleted_at')
                    ->pluck('id')
                    ->toArray();
                if(count($userId) > 0){
                    SMPushHelper::sendPush($notification['title'], $notification['description'],$userId);
                }
            }

        }
        $this->info('Birthday Notification Sent successfully!');
    }
}
