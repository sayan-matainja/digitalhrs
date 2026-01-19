<?php

namespace App\Repositories;


use App\Models\Complaint;
use App\Traits\ImageService;

class ComplaintRepository
{
    use ImageService;

    public function getAllComplaintPaginated($filterParameters,$select=['*'],$with=[])
    {
        return Complaint::with($with)->select($select)
            ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('branch_id', $filterParameters['branch_id']);
            })

            ->when(isset($filterParameters['department_id']), function ($query) use ($filterParameters) {
                $query->whereHas('complaintDepartment',function($subQuery) use ($filterParameters){
                    $subQuery->whereIn('department_id', $filterParameters['department_id']);
                });
            })
            ->when(isset($filterParameters['employee_id']), function ($query) use ($filterParameters) {
                $query->whereHas('complaintEmployee',function($subQuery) use ($filterParameters){
                    $subQuery->whereIn('employee_id', $filterParameters['employee_id']);
                });
            })
            ->when(isset($filterParameters['complaint_date']), function($query) use ($filterParameters){
                $query->whereDate('complaint_date',date('Y-m-d',strtotime($filterParameters['complaint_date'])));
            })
            ->latest()
            ->paginate( getRecordPerPage());
    }

    public function getEmployeeComplaintPaginated($perPage, $select = ['*'], $with = [])
    {
        $authUserCode = getAuthUserCode();


        return Complaint::select($select)
            ->with($with)
            ->where(function ($query) use ($authUserCode) {
                $query->whereHas('complaintEmployee', function ($employeeQuery) use ($authUserCode) {
                    $employeeQuery->where('employee_id', $authUserCode);
                });
            })->paginate($perPage);
    }

    public function find($id,$select=['*'],$with=[])
    {
        return Complaint::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }


    public function store($validatedData)
    {
        if(isset($validatedData['image'])){
            $validatedData['image'] = $this->storeImage($validatedData['image'], Complaint::UPLOAD_PATH, 500, 250);
        }
        return Complaint::create($validatedData)->fresh();
    }

    public function update($complaintDetail,$validatedData)
    {
        $validatedData['updated_by'] = auth()->user()->id ?? null;

        if (isset($validatedData['image'])) {
            if ($complaintDetail['image']) {
                $this->removeImage(Complaint::UPLOAD_PATH, $complaintDetail['image']);
            }
            $validatedData['image'] = $this->storeImage($validatedData['image'], Complaint::UPLOAD_PATH, 500, 250);
        }
        return $complaintDetail->update($validatedData);
    }


    public function delete($complaintDetail)
    {
        if ($complaintDetail['image']) {
            $this->removeImage(Complaint::UPLOAD_PATH, $complaintDetail['image']);
        }
        $complaintDetail->complaintEmployee()->delete();
        $complaintDetail->complaintDepartment()->delete();
        $complaintDetail->complaintReply()->delete();
        return $complaintDetail->delete();
    }


    public function saveEmployee(Complaint $complaintDetail,$userArray)
    {
        return $complaintDetail->complaintEmployee()->createMany($userArray);
    }

    public function updateEmployee(Complaint $complaintDetail,$userArray)
    {
        $complaintDetail->complaintEmployee()->delete();
        return $complaintDetail->complaintEmployee()->createMany($userArray);
    }
    public function saveDepartment(Complaint $complaintDetail,$departmentArray)
    {
        return $complaintDetail->complaintDepartment()->createMany($departmentArray);
    }

    public function updateDepartment(Complaint $complaintDetail,$departmentArray)
    {
        $complaintDetail->complaintDepartment()->delete();
        return $complaintDetail->complaintDepartment()->createMany($departmentArray);
    }

    public function saveResponse(Complaint $complaintDetail,$responseArray)
    {

        return $complaintDetail->complaintReply()->create($responseArray);
    }

    public function updateResponse(Complaint $complaintDetail,$responseArray)
    {
        $complaintDetail->complaintReply()->delete();
        return $complaintDetail->complaintReply()->create($responseArray);
    }

}
