<?php

namespace App\Console\Commands;

use App\Enum\ResignationStatusEnum;
use App\Enum\TerminationStatusEnum;
use App\Models\Project;
use App\Models\Resignation;
use App\Models\Task;
use App\Models\Termination;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateProjectStatus extends Command
{
    const IS_ACTIVE = 1;

    protected $signature = 'command:update-project-status';

    protected $description = 'Update Project Status.';

    public function handle()
    {
        $now = Carbon::today();

        // Auto-complete projects if all tasks are completed and dates have passed
        Project::where(function ($query) use ($now) {
            $query->where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->where('status', '!=', 'expired')
                ->where(function ($subQuery) use ($now) {
                    $subQuery->where('start_date', '<', $now)
                        ->orWhere(function ($innerQuery) use ($now) {
                            $innerQuery->where('deadline', '<', $now);
                        });
                });
        })
            ->whereDoesntHave('tasks', function ($taskQuery) {
                $taskQuery->whereIn('status', ['in_progress', 'not_started']);
            })
            ->whereHas('tasks', function ($taskQuery) {
                $taskQuery->where('status', 'completed');
            })
            ->update(['status' => 'completed', 'is_active' => 0]);

        // Set projects to expired if deadline has passed and not completed/cancelled
        Project::where(function ($query) use ($now) {
            $query->where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->where('status', '!=', 'expired')
                ->where('deadline', '<', $now)
                ->whereNotNull('deadline');
        })
            ->update(['status' => 'expired', 'is_active' => 0]);

        // Set projects to in_progress if start date is today or (started and deadline not passed)
        Project::where(function ($query) use ($now) {
            $query->where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->where('status', '!=', 'expired')
                ->where(function ($subQuery) use ($now) {
                    $subQuery->where('start_date', '=', $now)
                        ->orWhere(function ($innerQuery) use ($now) {
                            $innerQuery->where('start_date', '<', $now)
                                ->where(function ($deadlineQuery) use ($now) {
                                    $deadlineQuery->whereNull('deadline')
                                        ->orWhere('deadline', '>=', $now);
                                });
                        });
                });
        })
            ->update(['status' => 'in_progress', 'is_active' => 1]);

        // Set projects to not_started if start date is in the future
        Project::where('start_date', '>', $now)
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'expired')
            ->update(['status' => 'not_started', 'is_active' => 1]);

        // Set cancelled projects to inactive
        Project::where('status', '=', 'cancelled')
            ->update(['is_active' => 0]);

        // Auto-complete tasks if all checklists are completed and dates have passed
        Task::where(function ($query) use ($now) {
            $query->where('status', '!=', 'cancelled')
                ->where('status', '!=', 'completed')
                ->where('status', '!=', 'expired')
                ->where(function ($subQuery) use ($now) {
                    $subQuery->where('start_date', '<', $now)
                        ->orWhere(function ($innerQuery) use ($now) {
                            $innerQuery->where('end_date', '<', $now);
                        });
                });
        })
            ->has('taskChecklists')
            ->whereDoesntHave('taskChecklists', function ($checklistQuery) {
                $checklistQuery->where('is_completed', 0);
            })
            ->update(['status' => 'completed', 'is_active' => 0]);

        // Set tasks to expired if end_date has passed and not completed/cancelled
        Task::where(function ($query) use ($now) {
            $query->where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->where('status', '!=', 'expired')
                ->where('end_date', '<', $now)
                ->whereNotNull('end_date');
        })
            ->update(['status' => 'expired', 'is_active' => 0]);

        // Set tasks to in_progress if start date is today or (started and end_date not passed)
        Task::where(function ($query) use ($now) {
            $query->where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->where('status', '!=', 'expired')
                ->where(function ($subQuery) use ($now) {
                    $subQuery->where('start_date', '=', $now)
                        ->orWhere(function ($innerQuery) use ($now) {
                            $innerQuery->where('start_date', '<', $now)
                                ->where(function ($endDateQuery) use ($now) {
                                    $endDateQuery->whereNull('end_date')
                                        ->orWhere('end_date', '>=', $now);
                                });
                        });
                });
        })
            ->update(['status' => 'in_progress', 'is_active' => 1]);

        // Set tasks to not_started if start date is in the future
        Task::where('start_date', '>', $now)
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'expired')
            ->update(['status' => 'not_started', 'is_active' => 1]);

        // Set cancelled tasks to inactive
        Task::where('status', '=', 'cancelled')
            ->update(['is_active' => 0]);

        $this->info('Project and Task Status Updated Successfully!');
    }
}
