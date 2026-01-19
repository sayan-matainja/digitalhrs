<?php

namespace App\Services\Payroll;

use App\Repositories\OverTimeEmployeeRepository;
use App\Repositories\OverTimeSettingRepository;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;

class OverTimeSettingService
{
    public function __construct(protected OverTimeSettingRepository $otRepo, protected OverTimeEmployeeRepository $otEmployeeRepo){}

    /**
     * @throws \Exception
     */
    public function getAllOTList($select=['*'])
    {
        return $this->otRepo->getAll($select);
    }

    /**
     * @throws \Exception
     */
    public function findOTById($id, $with=[])
    {
        return $this->otRepo->find($id, $with);
    }

    public function store($validatedData)
    {

        $relationData = $this->prepareDataForOverTimeEmployee($validatedData);
        $otDetail = $this->otRepo->save($validatedData);
        if ($otDetail) {
            $this->otRepo->saveEmployee($otDetail, $relationData['employee']);
            $this->otRepo->saveDepartment($otDetail, $relationData['department']);
        }

        return $otDetail;

    }

    /**
     * @throws \Exception
     */
    public function updateOverTime($otId, $validatedData)
    {
        $relationData = $this->prepareDataForOverTimeEmployee($validatedData);
        $otDetail = $this->findOTById($otId);
        $status = $this->otRepo->update($otDetail,$validatedData);

        if($status){
            $this->otRepo->updateEmployee($otDetail, $relationData['employee']);
            $this->otRepo->updateDepartment($otDetail, $relationData['department']);
        }

        return $otDetail;
    }


    /**
     * @throws \Exception
     */
    public function deleteOTSetting($otId)
    {

        $otDetail = $this->findOTById($otId);
        return $this->otRepo->delete($otDetail);


    }

    /**
     * @throws \Exception
     */
    public function changeOTStatus($otId)
    {

        $otDetail = $this->findOTById($otId);

        return $this->otRepo->toggleIsActiveStatus($otDetail);

    }


    /**
     * @param $validatedData
     * @return array
     */
    protected function prepareDataForOverTimeEmployee($validatedData)
    {
        return [
            'employee' => array_map(fn($id) => ['employee_id' => $id], $validatedData['employee_id']),
            'department' => array_map(fn($id) => ['department_id' => $id], $validatedData['department_id']),
        ];
    }
}
