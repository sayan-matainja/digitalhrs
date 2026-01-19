<?php

namespace App\Services\Payroll;

use App\Repositories\BonusRepository;
use Illuminate\Support\Facades\DB;

class BonusService
{
    public function __construct(protected BonusRepository $bonusRepository){}

    /**
     * @throws \Exception
     */
    public function getAllBonusList($select=['*'], $with=[])
    {

        return $this->bonusRepository->getAll($select,$with);

    }

    /**
     * @throws \Exception
     */
    public function store($validatedData)
    {

        $relationData = $this->getBonusRelationData($validatedData);
        $validatedData['is_active'] = 1;
        $bonusDetail = $this->bonusRepository->store($validatedData);
        if ($bonusDetail) {
            if($relationData['employee']) {
                $this->bonusRepository->saveEmployee($bonusDetail, $relationData['employee']);
            }
            if($relationData['department']) {
                $this->bonusRepository->saveDepartment($bonusDetail, $relationData['department']);
            }
        }
    }


    private function getBonusRelationData($validatedData): array
    {
        $data['employee'] = [];
        $data['department'] = [];
        if(isset($validatedData['employee_id'])){
            $data['employee'] = array_map(fn($id) => ['employee_id' => $id], $validatedData['employee_id']);
        }
        if(isset($validatedData['department_id'])) {
            $data['department'] = array_map(fn($id) => ['department_id' => $id], $validatedData['department_id']);
        }


        return $data;
    }
    /**
     * @throws \Exception
     */
    public function findBonusById($id, $select=['*'],$with=[])
    {
        return $this->bonusRepository->find($id,$select,$with);
    }

    /**
     * @throws \Exception
     */
    public function findBonusByMonth($month, $select=['*'])
    {
        return $this->bonusRepository->findByMonth($month, $select);
    }

    /**
     * @throws \Exception
     */
    public function updateDetail($id, $validatedData)
    {
        $relationData = $this->getBonusRelationData($validatedData);
        $bonusDetail = $this->findBonusById($id);
        $status = $this->bonusRepository->update($bonusDetail,$validatedData);

        if($status){
            if($relationData['employee']) {
                $this->bonusRepository->updateEmployee($bonusDetail, $relationData['employee']);
            }
            if($relationData['department']) {
                $this->bonusRepository->updateDepartment($bonusDetail, $relationData['department']);
            }
        }

        return $bonusDetail;
    }

    /**
     * @throws \Exception
     */
    public function pluckAllActiveBonus()
    {
        return $this->bonusRepository->pluckAllBonusLists();
    }

    /**
     * @throws \Exception
     */
    public function deleteBonusDetail($bonusDetail)
    {

        return $this->bonusRepository->delete($bonusDetail);
    }

    /**
     * @throws \Exception
     */
    public function changeBonusStatus($bonusDetail)
    {
        return $this->bonusRepository->toggleStatus($bonusDetail);
    }

    public function findBonusByEmployeeAndMonth($employeeId,$month)
    {
        return $this->bonusRepository->findByEmployeeAndMonth($employeeId,$month);
    }
}
