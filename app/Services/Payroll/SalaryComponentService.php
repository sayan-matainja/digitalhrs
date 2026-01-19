<?php

namespace App\Services\Payroll;

use App\Repositories\SalaryComponentRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class SalaryComponentService
{
    public function __construct(public SalaryComponentRepository $salaryComponentRepo){}
    /**
     * @throws \Exception
     */
    public function getAllSalaryComponentList($select=['*'],$with=[])
    {
        return $this->salaryComponentRepo->getAllSalaryComponentLists($select,$with);
    }

    /**
     * @throws \Exception
     */
    public function getGeneralSalaryComponents($select=['*'])
    {
        return $this->salaryComponentRepo->getGeneralSalaryComponentList($select);
    }

    public function store($validatedData)
    {
        return $this->salaryComponentRepo->store($validatedData);
    }

    /**
     * @throws \Exception
     */
    public function findSalaryComponentById($id, $select=['*'])
    {
        return $this->salaryComponentRepo->findDetailById($id,$select);
    }

    /**
     * @throws \Exception
     */
    public function updateDetail($salaryComponentDetail, $validatedData)
    {
        return $this->salaryComponentRepo->update($salaryComponentDetail,$validatedData);
    }

    public function pluckAllActiveSalaryComponent()
    {
        return $this->salaryComponentRepo->pluckAllSalaryComponentLists();
    }

    /**
     * @throws \Exception
     */
    public function deleteSalaryComponentDetail($id)
    {

        $salaryComponentDetail = $this->findSalaryComponentById($id);
        return $this->salaryComponentRepo->delete($salaryComponentDetail);
    }

    /**
     * @throws \Exception
     */
    public function changeSalaryComponentStatus($salaryComponentDetail)
    {
        return $this->salaryComponentRepo->toggleStatus($salaryComponentDetail);
    }

    /**
     * @throws Exception
     */
    public function checkComponentUse($id)
    {
        $salaryComponentDetail = $this->findSalaryComponentById($id);
        return  $this->salaryComponentRepo->findComponentUse($salaryComponentDetail);
    }

}
