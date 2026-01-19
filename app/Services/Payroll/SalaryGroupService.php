<?php

namespace App\Services\Payroll;

use App\Repositories\SalaryGroupEmployeeRepository;
use App\Repositories\SalaryGroupRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalaryGroupService
{
    public function __construct(
        public SalaryGroupRepository $salaryGroupRepo,
        public SalaryGroupEmployeeRepository $salaryGroupEmployeeRepo
    ){}

    /**
     * @throws \Exception
     */
    public function getAllSalaryGroupDetailList($select=['*'], $with=[])
    {
        return $this->salaryGroupRepo->getAllSalaryGroupLists($select,$with);
    }

    /**
     * @throws \Exception
     */
    public function findOrFailSalaryGroupDetailById($id, $select=['*'], $with=[])
    {
        return $this->salaryGroupRepo->findSalaryGroupDetailById($id,$select,$with);
    }


    /**
     * @throws \Exception
     */
    public function store($validatedData)
    {

        $validatedData['slug'] = Str::slug($validatedData['name']);
        $relationData = $this->getSalaryGroupRelationData($validatedData);
        $validatedData['is_active'] = 1;
        $salaryGroup = $this->salaryGroupRepo->store($validatedData);

        if($salaryGroup){
            $this->salaryGroupRepo->attachComponentToGroup($salaryGroup,$validatedData['salary_component_id']);
            $this->salaryGroupRepo->saveEmployee($salaryGroup, $relationData['employee']);
            $this->salaryGroupRepo->saveDepartment($salaryGroup, $relationData['department']);
        }


        return $salaryGroup;

    }

    public function pluckAllActiveSalaryGroup($select)
    {
        return $this->salaryGroupRepo->pluckActiveSalaryGroup($select);
    }

    /**
     * @throws \Exception
     */
    public function updateDetail($salaryGroupDetail, $validatedData)
    {

        $validatedData['slug'] = Str::slug($validatedData['name']);
        $validatedData['salary_component_id'] = $validatedData['salary_component_id'] ?? [];


        $relationData = $this->getSalaryGroupRelationData($validatedData);
        $salaryGroupUpdate = $this->salaryGroupRepo->update($salaryGroupDetail,$validatedData);
        if($salaryGroupUpdate){

            $this->salaryGroupRepo->syncSalaryComponentToSalaryGroup($salaryGroupUpdate,$validatedData['salary_component_id']);

            $this->salaryGroupRepo->updateEmployee($salaryGroupDetail, $relationData['employee']);
            $this->salaryGroupRepo->updateDepartment($salaryGroupDetail, $relationData['department']);
        }


        return $salaryGroupUpdate;

    }

    /**
     * @throws \Exception
     */
    public function deleteSalaryGroupDetail($salaryGroupDetail)
    {

        return $this->salaryGroupRepo->delete($salaryGroupDetail);

    }

    /**
     * @throws \Exception
     */
    public function changeSalaryGroupStatus($salaryGroupDetail)
    {

        return $this->salaryGroupRepo->toggleIsActiveStatus($salaryGroupDetail);

    }

    private function getSalaryGroupRelationData($validatedData): array
    {
        return [
            'employee' => array_map(fn($id) => ['employee_id' => $id], $validatedData['employee_id']),
            'department' => array_map(fn($id) => ['department_id' => $id], $validatedData['department_id']),
        ];
    }

}
