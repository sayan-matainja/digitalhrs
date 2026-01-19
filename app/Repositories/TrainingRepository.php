<?php

namespace App\Repositories;

use App\Enum\TrainingStatusEnum;
use App\Models\Training;
use App\Traits\ImageService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TrainingRepository
{
    use ImageService;

    public function getAllTrainingPaginated($filterParameters,$select=['*'],$with=[])
    {
        return Training::with($with)->select($select)
            ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['department_id']), function ($query) use ($filterParameters) {
                $query->whereHas('trainingDepartment',function($subQuery) use ($filterParameters){
                    $subQuery->whereIn('department_id', $filterParameters['department_id']);
                });
            })
            ->when(isset($filterParameters['employee_id']), function ($query) use ($filterParameters) {
                $query->whereHas('employeeTraining',function($subQuery) use ($filterParameters){
                    $subQuery->whereIn('employee_id', $filterParameters['employee_id']);
                });
            })
            ->when(isset($filterParameters['training_type_id']), function($query) use ($filterParameters){
                $query->where('training_type_id', $filterParameters['training_type_id']);
            })
            ->when(isset($filterParameters['start_date']), function($query) use ($filterParameters){
                $query->whereDate('start_date',date('Y-m-d',strtotime($filterParameters['start_date'])));
            })
             ->when(isset($filterParameters['end_date']), function($query) use ($filterParameters){
                $query->whereDate('end_date',date('Y-m-d',strtotime($filterParameters['end_date'])));
            })
            ->when(isset($filterParameters['end_date']), function ($query) {
                return $query->orderBy('end_date', 'desc');
            }, function ($query) {
                return $query->orderBy('start_date', 'desc');
            })

            ->paginate( getRecordPerPage());
    }

    public function getEmployeeTrainingPaginated($perPage, $select = ['*'], $with = [], $isUpcoming = 0)
    {
        $authUserCode = getAuthUserCode();


        $trainingQuery = Training::select($select)
            ->with($with)
            ->where(function ($query) use ($authUserCode) {
                $query->whereHas('employeeTraining', function ($employeeQuery) use ($authUserCode) {
                    $employeeQuery->where('employee_id', $authUserCode);
                })
                    ->orWhereHas('trainingInstructor', function ($trainerQuery) use ($authUserCode) {
                        $trainerQuery->whereHas('trainer', function ($trainerNestedQuery) use ($authUserCode) {
                            $trainerNestedQuery->where('employee_id', $authUserCode);
                        });
                    });
            });


        if ($isUpcoming == 0) {

            $trainingQuery->where(function ($query) {
                $query->where('start_date', '<', Carbon::today())
                    ->where(function ($subQuery) {
                        $subQuery->whereNotNull('end_date')
                            ->where('end_date', '<', Carbon::today());
                    });
            });

        } else {

            $trainingQuery->where('start_date', '>=', Carbon::today())
                ->orWhere(function ($query) {
                    $query->where('start_date', '<=', Carbon::today())
                    ->where('end_date', '>=', Carbon::today());
                });
        }

        // Return paginated results
        return $trainingQuery->paginate($perPage);
    }

    public function getMonthTrainings($filterParameter, $select = ['*'], $with = [])
    {
        $authUserCode = getAuthUserCode();

        return Training::select($select)
            ->with($with)
            ->where(function ($query) use ($authUserCode) {
                $query->whereHas('employeeTraining', function ($employeeQuery) use ($authUserCode) {
                    $employeeQuery->where('employee_id', $authUserCode);
                })
                    ->orWhereHas('trainingInstructor', function ($trainerQuery) use ($authUserCode) {
                        $trainerQuery->whereHas('trainer', function ($trainerNestedQuery) use ($authUserCode) {
                            $trainerNestedQuery->where('employee_id', $authUserCode);
                        });
                    });
            })
            ->where(function ($q) use ($filterParameter) {
                $q->where('start_date', '<=', $filterParameter['end_date'])
                    ->where(function ($sub) use ($filterParameter) {
                        $sub->whereNull('end_date')
                            ->orWhere('end_date', '>=', $filterParameter['start_date']);
                    });

            })
            ->get();
    }


    public function find($id,$select=['*'],$with=[])
    {
        return Training::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

     public function getRecentTraining($select,$with, $employeeId = 0)
    {
        $recentTraining =  Training::select($select)
            ->with($with);
            if($employeeId != 0){
                $recentTraining = $recentTraining->where(function ($query) use ($employeeId) {
                    // Get trainings where user is either a trainee or a trainer
                    $query->whereHas('employeeTraining', function ($employeeQuery) use ($employeeId) {
                        $employeeQuery->where('employee_id', $employeeId);
                    })
                        ->orWhereHas('trainingInstructor', function ($trainerQuery) use ($employeeId) {
                            $trainerQuery->whereHas('trainer', function ($trainerNestedQuery) use ($employeeId) {
                                $trainerNestedQuery->where('employee_id', $employeeId);
                            });
                        });
                });
            }

            return $recentTraining->where('start_date', '>=', Carbon::today())
                ->orWhere(function ($query) {
                    $query->where('start_date', '<=', Carbon::today()) // Condition 2a: Training started already
                    ->where('end_date', '>=', Carbon::today()); // Condition 2b: Training is still ongoing
                })
            ->first();
    }
    public function getTrainingSummary($employeeId)
    {
        return Training::select(DB::raw('COUNT(DISTINCT trainings.id) as upcoming_training'))
            ->where(function ($query) use ($employeeId) {
                $query->whereHas('employeeTraining', function ($employeeQuery) use ($employeeId) {
                    $employeeQuery->where('employee_id', $employeeId);
                })
                    ->orWhereHas('trainingInstructor', function ($trainerQuery) use ($employeeId) {
                        $trainerQuery->whereHas('trainer', function ($trainerNestedQuery) use ($employeeId) {
                            $trainerNestedQuery->where('employee_id', $employeeId);
                        });
                    });
            })->where(function ($query) {
                $query->where('trainings.start_date', '>=', Carbon::today())
                    ->orWhere(function ($query) {
                        $query->where('trainings.start_date', '<=', Carbon::today()) // Condition 2a: Training started already
                        ->where('trainings.end_date', '>=', Carbon::today()); // Condition 2b: Training is still ongoing
                    });
            })->first();
    }

    public function store($validatedData)
    {
        if(isset($validatedData['certificate'])){
            $validatedData['certificate'] = $this->storeImage($validatedData['certificate'], Training::UPLOAD_PATH);
        }
        return Training::create($validatedData)->fresh();
    }

    public function update($trainingDetail,$validatedData)
    {
        $validatedData['updated_by'] = auth()->user()->id ?? null;

        if (isset($validatedData['certificate'])) {
            $this->removeImage(Training::UPLOAD_PATH, $trainingDetail['certificate']);
            $validatedData['certificate'] = $this->storeImage($validatedData['certificate'], Training::UPLOAD_PATH);
        }
        return $trainingDetail->update($validatedData);
    }

    public function updateAllStatus()
    {
        $now = Carbon::now(); // Use Carbon::now() to include time

        // Update completed status
        Training::where(function ($query) use ($now) {
            $query->where('start_date', '<', $now) // Case 1: Start date is less than now
            ->orWhere(function ($subQuery) use ($now) { // Case 2: End date or time is less than now and not null
                $subQuery->whereNotNull('end_date')
                    ->where('end_date', '<', $now)
                    ->where('end_time', '<', $now);

            });
        })
            ->update(['status' => TrainingStatusEnum::completed->value]);

        // Update ongoing status
        Training::where(function ($query) use ($now) {
            $query->where('start_date', '=', $now) // Case 1: Start date is today
            ->orWhere(function ($subQuery) use ($now) { // Case 2: Start date is past, end_date or end_time is >= now
                $subQuery->where('start_date', '<', $now)
                    ->where(function ($endQuery) use ($now) {
                        $endQuery->whereNotNull('end_date')
                            ->where('end_date', '>=', $now)
                            ->where('end_time', '>=', $now);

                    });
            });
        })
            ->update(['status' => TrainingStatusEnum::ongoing->value]);

        // Update pending status
        Training::where('start_date', '>', $now)
            ->update(['status' => TrainingStatusEnum::pending->value]);
    }

    public function delete($trainingDetail)
    {
        $trainingDetail->employeeTraining()->delete();
        $trainingDetail->trainingDepartment()->delete();
        $trainingDetail->trainingInstructor()->delete();
        return $trainingDetail->delete();
    }


    public function saveEmployee(Training $trainingDetail,$userArray)
    {
        return $trainingDetail->employeeTraining()->createMany($userArray);
    }

    public function updateEmployee(Training $trainingDetail,$userArray)
    {
        $trainingDetail->employeeTraining()->delete();
        return $trainingDetail->employeeTraining()->createMany($userArray);
    }
    public function saveDepartment(Training $trainingDetail,$departmentArray)
    {
        return $trainingDetail->trainingDepartment()->createMany($departmentArray);
    }

    public function updateDepartment(Training $trainingDetail,$departmentArray)
    {
        $trainingDetail->trainingDepartment()->delete();
        return $trainingDetail->trainingDepartment()->createMany($departmentArray);
    }

    public function saveTrainer(Training $trainingDetail,$trainerArray)
    {
        return $trainingDetail->trainingInstructor()->createMany($trainerArray);
    }

    public function updateTrainer(Training $trainingDetail,$trainerArray)
    {
        $trainingDetail->trainingInstructor()->delete();
        return $trainingDetail->trainingInstructor()->createMany($trainerArray);
    }


    public function checkTrainingType($typeId)
    {
        return Training::where('training_type_id', $typeId)->exists();
    }

   public function checkTrainer($trainerId)
    {
        return Training::where(function ($query) use ($trainerId) {
            $query->whereHas('trainingInstructor', function ($employeeQuery) use ($trainerId) {
                $employeeQuery->where('trainer_id', $trainerId);
            });
        })->exists();
    }

}
