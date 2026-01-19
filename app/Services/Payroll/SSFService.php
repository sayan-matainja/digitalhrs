<?php

namespace App\Services\Payroll;

use App\Repositories\SSFRepository;
use Illuminate\Support\Facades\DB;

class SSFService
{
    public function __construct(protected SSFRepository $ssfRepository){}


    /**
     * @throws \Exception
     */
    public function findSSFById($id)
    {
        $ssfDetail = $this->ssfRepository->find($id);

        return $ssfDetail;
    }

    public function getSSF()
    {

        return $this->ssfRepository->getSSF();
    }
    /**
     * @throws \Exception
     */
    public function findSSFDetail($select=['*'])
    {
        return $this->ssfRepository->getDetail($select);
    }
    public function getSSFDetailForTax($startDate)
    {
        return $this->ssfRepository->getSSFForTax($startDate);
    }
    /**
     * @throws \Exception
     */
    public function storeSSF($validatedData)
    {

           return $this->ssfRepository->store($validatedData);
    }

    /**
     * @throws \Exception
     */
    public function updateSSF($id, $validatedData)
    {

        $ssfDetail = $this->findSSFById($id);
        $this->ssfRepository->update($ssfDetail,$validatedData);
        return $ssfDetail;
    }

    public function getSSFDetailForPayroll($startDate, $endDate)
    {
        return $this->ssfRepository->getDetailForSSF($startDate, $endDate);
    }

}
