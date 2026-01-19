<?php

namespace App\Repositories;

use App\Models\Termination;
use App\Traits\ImageService;

class TerminationRepository
{
    use ImageService;


    public function getAllTerminationPaginated($filterParameters,$select=['*'],$with=[])
    {
        return Termination::with($with)->select($select)
            ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['department_id']), function($query) use ($filterParameters){
                $query->where('department_id', $filterParameters['department_id']);
            })
            ->when(isset($filterParameters['employee_id']), function($query) use ($filterParameters){
                $query->where('employee_id', $filterParameters['employee_id']);
            })
            ->when(isset($filterParameters['termination_type_id']), function($query) use ($filterParameters){
                $query->where('termination_type_id', $filterParameters['termination_type_id']);
            })
            ->when(isset($filterParameters['termination_date']), function($query) use ($filterParameters){
                $query->whereDate('termination_date',date('Y-m-d',strtotime($filterParameters['termination_date'])));
            })

            ->paginate( getRecordPerPage());
    }

    public function find($id,$select=['*'],$with=[])
    {
        return Termination::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function store($validatedData)
    {
        $validatedData['created_by']= auth()->user()->id ?? null;
        if(isset($validatedData['document'])){
            $validatedData['document'] = $this->storeImage($validatedData['document'], Termination::UPLOAD_PATH, 500, 250);
        }
        return Termination::create($validatedData)->fresh();
    }

    public function update($terminationDetail,$validatedData)
    {
        $validatedData['updated_by']= auth()->user()->id ?? null;
        if (isset($validatedData['document'])) {
            if ($terminationDetail['document']) {
                $this->removeImage(Termination::UPLOAD_PATH, $terminationDetail['document']);
            }
            $validatedData['document'] = $this->storeImage($validatedData['document'], Termination::UPLOAD_PATH, 500, 250);
        }
        return $terminationDetail->update($validatedData);
    }

    public function delete($terminationDetail)
    {
        return $terminationDetail->delete();
    }


}
