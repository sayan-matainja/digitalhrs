<?php

namespace App\Services\AssetManagement;

use App\Repositories\AssetTypeRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class AssetTypeService
{
    public function __construct(
        private AssetTypeRepository $assetTypeRepo
    ){}

    public function getAllAssetTypes($filterParameters,$select= ['*'],$with=[])
    {
        return $this->assetTypeRepo->getAllAssetTypes($filterParameters,$select,$with);
    }

    public function getAllActiveAssetTypes($select= ['*'],$with=[])
    {
        return $this->assetTypeRepo->getAllActiveAssetTypes($select,$with);
    }
    public function getBranchAssetTypes($branchId, $select= ['*'])
    {
        return $this->assetTypeRepo->getBranchAssetTypes($branchId, $select);
    }

    public function findAssetTypeById($id,$select=['*'],$with=[])
    {
        try{
            $assetType =  $this->assetTypeRepo->findAssetTypeById($id,$select,$with);
            if(!$assetType){
                throw new \Exception(__('message.asset_type_not_found'),400);
            }
            return $assetType;
        }catch(Exception $exception){
            throw $exception;
        }
    }

    public function store($validatedData)
    {
        try {
            DB::beginTransaction();
            $assetTypeDetail = $this->assetTypeRepo->create($validatedData);
            DB::commit();
            return $assetTypeDetail;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateAssetType($id, $validatedData)
    {
        try {
            $assetTypeDetail = $this->findAssetTypeById($id);
            DB::beginTransaction();
            $updateStatus = $this->assetTypeRepo->update($assetTypeDetail, $validatedData);
            DB::commit();
            return $updateStatus;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function deleteAssetType($id): bool
    {

        $assetTypeDetail = $this->findAssetTypeById($id);

        $this->assetTypeRepo->delete($assetTypeDetail);

        return true;

    }

    public function toggleIsActiveStatus($id): bool
    {
        try {
            DB::beginTransaction();
            $clientDetail = $this->findAssetTypeById($id);
            $this->assetTypeRepo->toggleIsActiveStatus($clientDetail);
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

}
