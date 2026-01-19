<?php

namespace App\Services\AssetManagement;

use App\Helpers\AppHelper;
use App\Repositories\AssetRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class AssetService
{
    public function __construct(
        private AssetRepository $assetRepo
    ){}

    public function getAllAssetsPaginated($filterParameters,$select= ['*'],$with=[])
    {
        if(AppHelper::ifDateInBsEnabled()){
            $filterParameters['purchased_from'] = isset($filterParameters['purchased_from']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['purchased_from']): null;
            $filterParameters['purchased_to'] = isset($filterParameters['purchased_to']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['purchased_to']): null;
        }
        return $this->assetRepo->getAllAssetsPaginated($filterParameters,$select,$with);
    }

    public function getReturnAssetsPaginated($select= ['*'],$with=[])
    {

        return $this->assetRepo->getReturnAssetsPaginated($select,$with);
    }

    /**
     * @throws Exception
     */
    public function findAssetById($id, $select=['*'], $with=[])
    {

            $assetDetail =  $this->assetRepo->findAssetById($id,$select,$with);
            if(!$assetDetail){
                throw new \Exception(__('message.asset_type_not_found'),400);
            }
            return $assetDetail;

    }

    public function saveAssetDetail($validatedData)
    {
        $validatedData['is_working'] = 'yes';
        return $this->assetRepo->store($validatedData);

    }

    /**
     * @throws Exception
     */
    public function updateAssetDetail($id, $validatedData)
    {
        $assetDetail = $this->findAssetById($id);

        return $this->assetRepo->update($assetDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function deleteAsset($id)
    {

            $assetDetail = $this->findAssetById($id);

        return $this->assetRepo->delete($assetDetail);

    }






}
