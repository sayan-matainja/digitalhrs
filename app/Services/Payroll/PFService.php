<?php

namespace App\Services\Payroll;

use App\Repositories\PFRepository;
use Illuminate\Support\Facades\DB;

class PFService
{
    public function __construct(protected PFRepository $PFRepository){}


    /**
     * @throws \Exception
     */
    public function findPFById($id)
    {
        $PFDetail = $this->PFRepository->find($id);

        return $PFDetail;
    }

    public function getPF()
    {

        return $this->PFRepository->getPF();
    }
    /**
     * @throws \Exception
     */
    public function findPFDetail($select=['*'])
    {
        return $this->PFRepository->getDetail($select);
    }
    public function getPFDetailForTax($startDate)
    {
        return $this->PFRepository->getPFForTax($startDate);
    }
    /**
     * @throws \Exception
     */
    public function storePF($validatedData)
    {

           return $this->PFRepository->store($validatedData);
    }

    /**
     * @throws \Exception
     */
    public function updatePF($id, $validatedData)
    {

        $PFDetail = $this->findPFById($id);
        $this->PFRepository->update($PFDetail,$validatedData);
        return $PFDetail;
    }

    public function getPFDetailForPayroll($startDate, $endDate)
    {
        return $this->PFRepository->getDetailForPF($startDate, $endDate);
    }

}
