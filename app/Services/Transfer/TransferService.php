<?php

namespace App\Services\Transfer;

use App\Helpers\AppHelper;
use App\Repositories\TransferRepository;
use Exception;

class TransferService
{
    public function __construct(
        protected TransferRepository $transferRepository
    )
    {
    }

    public function getAllTransferPaginated($filterParameters,$select = ['*'], $with = [])
    {
        if(AppHelper::ifDateInBsEnabled()){
            $filterParameters['transfer_date'] = isset($filterParameters['transfer_date']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['transfer_date']): null;

        }
        return $this->transferRepository->getAllTransferPaginated($filterParameters,$select, $with);
    }




    /**
     * @throws Exception
     */
    public function findTransferById($id, $select = ['*'], $with = [])
    {
        return $this->transferRepository->find($id, $select, $with);
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function saveTransferDetail($validatedData)
    {
        $validatedData['created_by'] = auth()->user()->id ?? null;

        return $this->transferRepository->store($validatedData);

    }

    /**
     * @param $id
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function updateTransferDetail($id, $validatedData)
    {

        $transferDetail = $this->findTransferById($id);
        $this->transferRepository->update($transferDetail, $validatedData);


        return $transferDetail;
    }

    /**
     * @throws Exception
     */
    public function deleteTransfer($id)
    {
        $transferDetail = $this->findTransferById($id);
        return $this->transferRepository->delete($transferDetail);
    }


    /**
     * @param $transferId
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function updateStatus($transferId, $validatedData){
        $transferDetail = $this->findTransferById($transferId);

        $this->transferRepository->update($transferDetail, $validatedData);

        return $transferDetail;

    }
}
