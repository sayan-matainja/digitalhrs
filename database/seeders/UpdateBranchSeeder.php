<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\Award;
use App\Models\AwardType;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Event;
use App\Models\LeaveApproval;
use App\Models\LeaveRequestMaster;
use App\Models\LeaveType;
use App\Models\Notice;
use App\Models\OfficeTime;
use App\Models\Post;
use App\Models\Project;
use App\Models\QrAttendance;
use App\Models\Resignation;
use App\Models\Support;
use App\Models\Tada;
use App\Models\Task;
use App\Models\TeamMeeting;
use App\Models\Termination;
use App\Models\TerminationType;
use App\Models\TimeLeave;
use App\Models\Trainer;
use App\Models\TrainingType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateBranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::transaction(function () {
            $branch = Branch::where('id', 1)->first();

            if ($branch) {
                LeaveType::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                AssetType::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                Project::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                Client::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                OfficeTime::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                TerminationType::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                AwardType::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                TrainingType::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                QrAttendance::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                TeamMeeting::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                Notice::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                Asset::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                Event::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                Trainer::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                LeaveApproval::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                LeaveRequestMaster::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                TimeLeave::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                Tada::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                Termination::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                Resignation::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                Award::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                Support::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                Task::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
                Post::query()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
            }
        });

    }
}
