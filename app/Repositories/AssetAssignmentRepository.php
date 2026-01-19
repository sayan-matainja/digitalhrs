<?php

namespace App\Repositories;

use App\Enum\AssetReturnConditionEnum;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Project;
use App\Traits\ImageService;

class AssetAssignmentRepository
{

    public function getAllAssignmentPaginated($assetId,$select=['*'],$with=[])
    {
        return AssetAssignment::select($select)->with($with)
            ->where('asset_id',$assetId)
            ->latest()
            ->get();
    }

    public function getEmployeeAssignment($employeeId,$select=['*'],$with=[])
    {
        return AssetAssignment::select($select)->with($with)
            ->where('user_id',$employeeId)
            ->latest()
            ->get();
    }

    public function getAssignmentReturnPaginated($select=['*'],$with=[])
    {
        return AssetAssignment::select($select)->with($with)
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('asset_assignments')
                    ->whereNotNull('returned_date')
                    ->groupBy('asset_id');
            })
            ->orderBy('returned_date', 'desc')
            ->get();
    }

    public function getAssignmentMaintenancePaginated($select=['*'],$with=[])
    {
        return AssetAssignment::select($select)->with($with)
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('asset_assignments')
                    ->whereNotNull('returned_date')
                    ->groupBy('asset_id');
            })
            ->where('return_condition',AssetReturnConditionEnum::requireMaintenance->value)
            ->orderBy('returned_date', 'desc')
            ->get();
    }


    public function find($id,$select=['*'],$with=[])
    {
        return AssetAssignment::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function store($validatedData)
    {
        return AssetAssignment::create($validatedData)->fresh();
    }

    public function update($assetDetail,$validatedData)
    {

        return $assetDetail->update($validatedData);
    }

    public function delete($assetDetail)
    {

        return $assetDetail->delete();
    }

    public function changeIsAvailableStatus($assetDetail)
    {
        return $assetDetail->update([
            'is_available' => !$assetDetail->is_available
        ]);
    }

    public function changeRepairStatus($assignmentDetail)
    {
        $prevRepairStatus = $assignmentDetail->return_condition;
        $assignmentDetail->update([
            'return_condition'=> ($prevRepairStatus == AssetReturnConditionEnum::requireMaintenance->value) ? AssetReturnConditionEnum::repaired->value : AssetReturnConditionEnum::requireMaintenance->value
        ]);


        return Asset::where('id',$assignmentDetail->asset_id)->update([
            'is_working' => ($prevRepairStatus == AssetReturnConditionEnum::requireMaintenance->value) ? 'yes' : 'no',
            'is_available' => ($prevRepairStatus == AssetReturnConditionEnum::requireMaintenance->value) ? 1 : 0,
        ]);


    }

}
