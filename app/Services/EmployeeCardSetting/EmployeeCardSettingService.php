<?php

namespace App\Services\EmployeeCardSetting;

use App\Repositories\EmployeeCardSettingRepository;
use Exception;

class EmployeeCardSettingService
{
    public function __construct(
        protected EmployeeCardSettingRepository $cardRepository
    ){}


    /**
     * @throws Exception
     */
    public function getAllCardSetting($select=['*'])
    {
        return $this->cardRepository->getAll($select);
    }
    /**
     * @throws Exception
     */
    public function findCardSettingById($id,$select=['*'])
    {
        return $this->cardRepository->find($id,$select);
    }
    /**
     * @throws Exception
     */
    public function defaultCardSetting()
    {
        return $this->cardRepository->getDefault();
    }

    /**
     * @throws Exception
     */
    public function saveCardTemplate( $data)
    {
        return $this->cardRepository->store($data);
    }

    /**
     * @throws Exception
     */
    public function updateCardTemplate($id, $data)
    {
        $cardSetting = $this->findCardSettingById($id);
        return $this->cardRepository->update($cardSetting, $data);
    }

    /**
     * @throws Exception
     */
    public function delete($id)
    {
        $cardSetting = $this->findCardSettingById($id);
        if($cardSetting->is_default == 1){
            throw new Exception('Default Template cannot be deleted', 400);

        }
        return $this->cardRepository->delete($cardSetting);
    }

    /**
     * @throws Exception
     */
    public function toggleIsActive($id)
    {
        $cardSetting = $this->findCardSettingById($id);
        if($cardSetting->is_default == 1){
            throw new Exception('Default Template cannot be made inactive', 400);

        }
        return $this->cardRepository->toggleStatus($cardSetting);
    }

    /**
     * @throws Exception
     */
    public function makeDefault($id)
    {
        $cardSetting = $this->findCardSettingById($id);
        return $this->cardRepository->setAsDefault($cardSetting);
    }

}
