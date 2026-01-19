<?php

namespace App\Services\Device;

use App\Helpers\AppHelper;
use App\Repositories\DeviceRepository;
use Exception;

class DeviceService
{
    public function __construct(
        private DeviceRepository $deviceRepository
    ){}

    public function getAllDevicesPaginated($select= ['*'],$with=[])
    {

        return $this->deviceRepository->getAllDevicesPaginated($select,$with);
    }
    /**
     * @throws Exception
     */
    public function findDeviceById($id, $select=['*'], $with=[])
    {

            $deviceDetail =  $this->deviceRepository->find($id,$select,$with);
            if(!$deviceDetail){
                throw new \Exception(__('message.device_not_found'),400);
            }
            return $deviceDetail;

    }

    /**
     * @throws Exception
     */
    public function findDeviceBySerialNumber($serialNumber)
    {

            $deviceDetail =  $this->deviceRepository->findBySerialNumber($serialNumber);
            if(!$deviceDetail){
                throw new \Exception(__('message.device_not_found'),400);
            }
            return $deviceDetail;

    }

    public function saveDeviceDetail($validatedData)
    {
        $validatedData['status'] = 'pending';
        return $this->deviceRepository->store($validatedData);

    }

    /**
     * @throws Exception
     */
    public function updateDeviceDetail($id, $validatedData)
    {
        $deviceDetail = $this->findDeviceById($id);

        return $this->deviceRepository->update($deviceDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function deleteDevice($id)
    {

        $deviceDetail = $this->findDeviceById($id);

        return $this->deviceRepository->delete($deviceDetail);

    }






}
