<?php

namespace App\Rules;

use App\Models\Project;
use Illuminate\Contracts\Validation\Rule;

class TaskDateRule implements Rule
{

    protected $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function passes($attribute, $value)
    {
        $project = Project::findOrFail($this->projectId, ['start_date', 'deadline']);

        $taskDate = strtotime($value);
        $projectStartDate = strtotime($project->start_date);
        $projectDeadline = strtotime($project->deadline);

        return $taskDate >= $projectStartDate && $taskDate <= $projectDeadline;
    }


    public function message()
    {
        return 'The :attribute cannot be before project start date or after project deadline.';
    }
}
