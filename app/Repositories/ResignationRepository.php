<?php

namespace App\Repositories;

use App\Enum\ResignationStatusEnum;
use App\Models\Resignation;
use App\Traits\ImageService;

class ResignationRepository
{
    use ImageService;

    public function getAllResignationPaginated($filterParameters,$select=['*'],$with=[])
    {
        return Resignation::with($with)->select($select)
            ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['department_id']), function($query) use ($filterParameters){
                $query->where('department_id', $filterParameters['department_id']);
            })
            ->when(isset($filterParameters['employee_id']), function($query) use ($filterParameters){
                $query->where('employee_id', $filterParameters['employee_id']);
            })

            ->when(isset($filterParameters['resignation_date']), function($query) use ($filterParameters){
                $query->whereDate('resignation_date',date('Y-m-d',strtotime($filterParameters['resignation_date'])));
            })
            ->paginate( getRecordPerPage());
    }

    public function find($id,$select=['*'],$with=[])
    {
        return Resignation::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function store($validatedData)
    {
        $validatedData['created_by']= auth()->user()->id ?? null;
        if(isset($validatedData['document'])){
            $validatedData['document'] = $this->storeImage($validatedData['document'], Resignation::UPLOAD_PATH, 500, 250);
        }
        return Resignation::create($validatedData)->fresh();
    }

    public function update($resignationDetail,$validatedData)
    {
        $validatedData['updated_by']= auth()->user()->id ?? null;
        if (isset($validatedData['document'])) {
            if ($resignationDetail['document']) {
                $this->removeImage(Resignation::UPLOAD_PATH, $resignationDetail['document']);
            }
            $validatedData['document'] = $this->storeImage($validatedData['document'], Resignation::UPLOAD_PATH, 500, 250);
        }
        return $resignationDetail->update($validatedData);
    }


    public function delete($resignationDetail)
    {
        if ($resignationDetail['document']) {
            $this->removeImage(Resignation::UPLOAD_PATH, $resignationDetail['document']);
        }
        return $resignationDetail->delete();
    }

    public function findByEmployeeId($employeeId,$select=['*'])
    {
        return Resignation::select($select)
            ->where('employee_id',$employeeId)
            ->orderBy('created_at','desc')
            ->first();
    }

}
