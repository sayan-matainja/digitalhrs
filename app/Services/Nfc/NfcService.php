<?php

namespace App\Services\Nfc;

use App\Repositories\NFCRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class NfcService
{

    public function __construct(public NFCRepository $NFCRepository)
    {
    }

    public function getAllNfc($filterData)
    {
        return $this->NFCRepository->getAll($filterData);
    }

    public function verifyNfc($identifier)
    {
        return $this->NFCRepository->verifyNfc($identifier);
    }

    /**
     * @param $id
     * @return mixed
     * @throws Exception
     */
    public function findNfcDetailById($id): mixed
    {
        $nfcDetail = $this->NFCRepository->findNFCDetailById($id);
        if (!$nfcDetail) {
            throw new Exception(__('message.nfc_not_found'), 400);
        }
        return $nfcDetail;
    }

    /**
     * @throws Exception
     */
    public function saveNfcDetail($validatedData)
    {

        return $this->NFCRepository->store($validatedData);


    }


    /**
     * @throws Exception
     */
    public function deleteNfcDetail($id): bool
    {

        $nfcDetail = $this->findNfcDetailById($id);

        return $this->NFCRepository->delete($nfcDetail);


    }


}

