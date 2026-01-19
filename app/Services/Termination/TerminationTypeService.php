<?php

namespace App\Services\Termination;

use App\Repositories\TerminationTypeRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class TerminationTypeService
{
    public function __construct(
        protected TerminationTypeRepository $terminationTypeRepository
    )
    {
    }

    public function getAllTerminationTypes($filterParameters,$select = ['*'], $with = [])
    {
        return $this->terminationTypeRepository->getAllTerminationTypes($filterParameters,$select, $with);
    }

    public function getAllActiveTerminationTypes($select = ['*'])
    {
        return $this->terminationTypeRepository->getAllActiveTerminationTypes($select);
    }

    public function getActiveBranchTerminationType($branchId, $select = ['*'])
    {
        return $this->terminationTypeRepository->getBranchTerminationTypes($branchId, $select);
    }

    /**
     * @throws Exception
     */
    public function findTerminationTypeById($id, $select = ['*'], $with = [])
    {

        return $this->terminationTypeRepository->find($id, $select, $with);

    }

    /**
     * @throws Exception
     */
    public function store($validatedData)
    {
        return $this->terminationTypeRepository->create($validatedData);

    }

    /**
     * @throws Exception
     */
    public function updateTerminationType($id, $validatedData)
    {

        $terminationTypeDetail = $this->findTerminationTypeById($id);
        return $this->terminationTypeRepository->update($terminationTypeDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function deleteTerminationType($id): bool
    {

        $terminationTypeDetail = $this->findTerminationTypeById($id);

        return $this->terminationTypeRepository->delete($terminationTypeDetail);


    }

    /**
     * @throws Exception
     */
    public function toggleStatus($id): bool
    {
        $terminationTypeDetail = $this->findTerminationTypeById($id);
        return $this->terminationTypeRepository->toggleStatus($terminationTypeDetail);

    }

}
