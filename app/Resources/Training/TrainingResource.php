<?php

namespace App\Resources\Training;

use App\Helpers\AppHelper;
use App\Models\Training;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id ?? '',
            'training_type' => ($this->trainingType) ? ucfirst($this->trainingType->title) : '',
            'employee' => $this->employeeTraining->map(function ($training) {
                return new EmployeeResource($training->employee);
            }),
            'branch' => ($this->branch) ? ucfirst($this->branch->name) : '',
            'department' => $this->trainingDepartment->map(function ($training) {
                return new DepartmentResource($training->department);
            }),
            'description' => removeHtmlTags($this->description),
            'cost' => $this->cost ?? '',
            'venue' => $this->venue ?? '',
            'start_date' => AppHelper::formatDateForView($this->start_date),
            'end_date' => isset($this->end_date) ? AppHelper::formatDateForView($this->end_date) : '',
            'start_date_ad' => $this->start_date,
            'end_date_ad' => $this->end_date ?? '',
            'start_time' => AppHelper::convertLeaveTimeFormat($this->start_time),
            'end_time' => AppHelper::convertLeaveTimeFormat($this->end_time),
            'certificate' => $this->certificate ? asset(Training::UPLOAD_PATH.$this->certificate) : '',
            'trainer' =>$this->trainingInstructor->map(function ($training) {
                return new TrainerResource($training->trainer);
            }),
        ];
    }
}

