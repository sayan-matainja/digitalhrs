<?php

namespace App\Repositories;


use App\Helpers\AppHelper;
use App\Models\Award;

use App\Traits\ImageService;

class AwardRepository
{
    use ImageService;

    public function getAllAwardsPaginated($filterParameters,$select=['*'],$with=[])
    {

        return Award::select($select)->with($with)
            ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['department_id']), function($query) use ($filterParameters){
                $query->where('department_id', $filterParameters['department_id']);
            })
            ->when(isset($filterParameters['employee_id']), function($query) use ($filterParameters){
                $query->where('employee_id', $filterParameters['employee_id']);
            })
            ->when(isset($filterParameters['award_type_id']), function($query) use ($filterParameters){
                $query->where('award_type_id', $filterParameters['award_type_id']);
            })
            ->when(isset($filterParameters['awarded_date']), function($query) use ($filterParameters){
                $query->whereDate('awarded_date',date('Y-m-d',strtotime($filterParameters['awarded_date'])));
            })
            ->latest()
            ->paginate( getRecordPerPage());
    }

    public function getEmployeeAwardsPaginated($employeeId, $perPage,$select=['*'],$with=[], $userProfile=0)
    {
        $awardList =  Award::select($select)->with($with)
            ->where('employee_id',$employeeId)
            ->where('awarded_date','<=',date('Y-m-d'));
            if($userProfile == 1){
                $awardList = $awardList->take(5)->get();
            }else{
                $awardList = $awardList
                    ->orderBy('awarded_date','desc')
                    ->paginate($perPage);
            }

            return $awardList;

    }

    public function findAwardById($id,$select=['*'],$with=[])
    {
        return Award::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

     public function getRecentAward($select,$with, $employeeId = 0)
    {
        $recentAward =  Award::select($select)
            ->with($with);
            if($employeeId != 0){
                $recentAward = $recentAward->where('employee_id',$employeeId);
            }

            return $recentAward->orderBy('awarded_date','desc')
            ->first();
    }

    public function store($validatedData)
    {
        if(isset($validatedData['attachment'])){
            $validatedData['attachment'] = $this->storeImage($validatedData['attachment'], Award::UPLOAD_PATH,500,500);
        }

        if(!isset($validatedData['awarded_by'])){
            $validatedData['awarded_by'] = AppHelper::getAuthUserCompanyName();
        }

        return Award::create($validatedData)->fresh();
    }

    public function update($assetDetail,$validatedData)
    {
        if (isset($validatedData['attachment'])) {
            if($assetDetail['attachment']){
                $this->removeImage(Award::UPLOAD_PATH, $assetDetail['attachment']);
            }
            $validatedData['attachment'] = $this->storeImage($validatedData['attachment'], Award::UPLOAD_PATH,500,500);
        }
        return $assetDetail->update($validatedData);
    }

    public function delete($assetDetail)
    {
        if($assetDetail['attachment']){
            $this->removeImage(Award::UPLOAD_PATH, $assetDetail['attachment']);
        }
        return $assetDetail->delete();
    }

    public function checkType($typeId)
    {
        return Award::where('award_type_id', $typeId)->exists();
    }


}
