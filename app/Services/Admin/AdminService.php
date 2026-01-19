<?php

namespace App\Services\Admin;

use App\Repositories\AdminRepository;
use Exception;

class AdminService
{
    public function __construct(
        protected AdminRepository $adminRepository
    ){}

    public function getAllAdmin($select= ['*'])
    {
        return $this->adminRepository->getAll($select);
    }


    /**
     * @throws Exception
     */
    public function findAdminById($id, $select=['*'], $with=[])
    {

        return $this->adminRepository->find($id,$select,$with);

    }

    /**
     * @throws Exception
     */
    public function saveAdmin($validatedData)
    {

        return $this->adminRepository->store($validatedData);

    }

    /**
     * @throws Exception
     */
    public function updateAdmin($id, $validatedData)
    {

        $trainingTypeDetail = $this->findAdminById($id);
        return $this->adminRepository->update($trainingTypeDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function deleteAdmin($adminDetail): bool
    {

        return $this->adminRepository->delete($adminDetail);


    }

    /**
     * @throws Exception
     */
    public function toggleIsActiveStatus($id): bool
    {
        $userDetail = $this->findAdminById($id);
        return $this->adminRepository->toggleStatus($userDetail);

    }


    public function getAdminByAdminName($userName, $select = ['*'])
    {
        return $this->adminRepository->getAdminByAdminName($userName, $select);
    }

    public function getAdminByAdminEmail($userEmail, $select = ['*'])
    {
        return $this->adminRepository->getAdminByAdminEmail($userEmail, $select);
    }
}
