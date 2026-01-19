<?php

namespace App\Console\Commands;

use App\Enum\ResignationStatusEnum;
use App\Models\Promotion;
use App\Models\Resignation;
use App\Models\User;
use Illuminate\Console\Command;

class UpdatePromotedUser extends Command
{
    const IS_ACTIVE = 1;

    protected $signature = 'command:update-promoted-user';

    protected $description = 'update promoted employees of company.';

    public function handle()
    {
        $todayDate = now()->format('Y-m-d');
        $promotionData = Promotion::query()
           ->where('promotion_date', $todayDate)
            ->get();

        if (!empty($promotionData)) {

            foreach ($promotionData as $promotion) {

                $userDetail = User::where('id', $promotion->employee_id)->first();

                $userDetail->update([
                    'post_id' => $promotion->post_id,
                ]);

            }

        }
        $this->info('Promoted employee account updated!');
    }
}
