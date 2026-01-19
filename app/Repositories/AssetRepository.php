<?php

namespace App\Repositories;

use App\Models\Asset;
use App\Models\Project;
use App\Traits\ImageService;

class AssetRepository
{
    use ImageService;

    public function getAllAssetsPaginated($filterParameters,$select=['*'],$with=[])
    {
        return Asset::select($select)->with($with)
            ->when(isset($filterParameters['type_id']), function($query) use ($filterParameters){
                $query->where('type_id', $filterParameters['type_id']);
            })
             ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['name']), function ($query) use ($filterParameters) {
                $query->where('name', 'like', '%' . $filterParameters['name'] . '%');
            })
            ->when(isset($filterParameters['is_working']), function ($query) use ($filterParameters) {
                $query->where('is_working', 'like', '%' . $filterParameters['is_working'] . '%');
            })
            ->when(isset($filterParameters['is_available']), function ($query) use ($filterParameters) {
                $query->where('is_available', $filterParameters['is_available']);
            })
            ->when(isset($filterParameters['purchased_from']), function($query) use ($filterParameters){
                $query->whereDate('purchased_date','>=',date('Y-m-d',strtotime($filterParameters['purchased_from'])));
            })
            ->when(isset($filterParameters['purchased_to']), function($query) use ($filterParameters){
                $query->whereDate('purchased_date','<=',date('Y-m-d',strtotime($filterParameters['purchased_to'])));
            })
            ->latest()
            ->paginate( getRecordPerPage());
    }

    public function getReturnAssetsPaginated($select=['*'],$with=[])
    {
        $branchId = null;
        if(!auth('admin')->check() && auth()->check()){
            $branchId = auth()->user()->branch_id;
        }
        return Asset::select($select)->with($with)
             ->when(!is_null($branchId), function($query) use ($branchId){
                $query->where('branch_id', $branchId);
            })
            ->whereHas('assignment', function ($query) {
                $query->whereNotNull('returned_date');
            })
            ->latest()
            ->paginate( getRecordPerPage());
    }

    public function findAssetById($id,$select=['*'],$with=[])
    {
        return Asset::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function store($validatedData)
    {
        $validatedData['image'] = $this->storeImage($validatedData['image'], Asset::UPLOAD_PATH,500,500);
        return Asset::create($validatedData)->fresh();
    }

    public function update($assetDetail,$validatedData)
    {
        if (isset($validatedData['avatar'])) {
            if($assetDetail['image']){
                $this->removeImage(Asset::UPLOAD_PATH, $assetDetail['image']);
            }
            $validatedData['image'] = $this->storeImage($validatedData['image'], Asset::UPLOAD_PATH,500,500);
        }
        return $assetDetail->update($validatedData);
    }

    public function delete($assetDetail)
    {
        if($assetDetail['image']){
            $this->removeImage(Asset::UPLOAD_PATH, $assetDetail['image']);
        }
        return $assetDetail->delete();
    }



}
