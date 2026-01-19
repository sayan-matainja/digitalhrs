<?php

namespace App\Services\Payroll;

use App\Repositories\UnderTimeEmployeeRepository;
use App\Repositories\UnderTimeSettingRepository;
use Illuminate\Support\Facades\DB;

class UnderTimeSettingService
{
    public function __construct(protected UnderTimeSettingRepository $utRepo, protected UnderTimeEmployeeRepository $utEmployeeRepo){}

    /**
     * @throws \Exception
     */
    public function getAllUTList($select=['*'],$first='')
    {
        return $this->utRepo->getAll($select, $first);
    }

    /**
     * @throws \Exception
     */
    public function findUTById($id)
    {
        return $this->utRepo->find($id);
    }

    public function store($validatedData)
    {
        try{
            DB::beginTransaction();
            $underTime = $this->utRepo->save($validatedData);
//            $utEmployeeData = $this->prepareDataForUnderTimeEmployee($validatedData['undertime_employee']);
//            if($underTime){
//                $this->utEmployeeRepo->assignUnderTimeToEmployees($underTime,$utEmployeeData);
//            }
            DB::commit();;
            return $underTime;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    public function updateUnderTime($utId, $validatedData)
    {
        try{
            $utDetail = $this->findUTById($utId);
//            $utEmployeeData = $this->prepareDataForUnderTimeEmployee($validatedData['undertime_employee']);
            DB::beginTransaction();
            $utData = $this->utRepo->update($utDetail,$validatedData);
//            if($utData){
//                $this->utEmployeeRepo->updateUnderTimeEmployee($utDetail,$utEmployeeData);
//            }
            DB::commit();;
            return $utDetail;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    public function deleteUTSetting($utId)
    {
        try{
            DB::beginTransaction();
            $utDetail = $this->findUTById($utId);
            $delete = $this->utRepo->delete($utDetail);
//            if($delete){
//                $this->utEmployeeRepo->removeAssignedEmployeeFromUnderTime($utDetail);
//            }
            DB::commit();
            return $delete;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    public function changeUTStatus($utId)
    {
        try{
            $utDetail = $this->findUTById($utId);
            DB::beginTransaction();
            $status = $this->utRepo->toggleIsActiveStatus($utDetail);
            DB::commit();
            return $status;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    private function prepareDataForUnderTimeEmployee($validatedData): array
    {
        try {
            return collect($validatedData)
                ->map(function ($value) {
                    return ['employee_id' => $value];
                })
                ->all();
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
