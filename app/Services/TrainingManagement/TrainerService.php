<?php

namespace App\Services\TrainingManagement;

use App\Repositories\TrainerRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class TrainerService
{
    public function __construct(
        protected TrainerRepository $trainerRepository
    ){}

    public function getAllTrainerPaginated($filterParameters,$select= ['*'],$with=[])
    {

        return $this->trainerRepository->getAllTrainerPaginated($filterParameters,$select,$with);
    }

    /**
     * @throws Exception
     */
    public function findTrainerById($id, $select=['*'], $with=[])
    {
        return $this->trainerRepository->find($id,$select,$with);
    }


      /**
     * @throws Exception
     */
    public function findTrainers($ids, $select=['*'])
    {
        return $this->trainerRepository->findTrainers($ids,$select);
    }


    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function saveTrainerDetail($validatedData)
    {
        return $this->trainerRepository->store($validatedData);

    }

    /**
     * @param $id
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function updateTrainerDetail($id, $validatedData)
    {
        $trainerDetail = $this->findTrainerById($id);
        return $this->trainerRepository->update($trainerDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function deleteTrainer($id)
    {
        $trainerDetail = $this->findTrainerById($id);
        return $this->trainerRepository->delete($trainerDetail);
    }

    /**
     * @throws Exception
     */
    public function toggleStatus($id): bool
    {
        $trainingTypeDetail = $this->findTrainerById($id);
        return $this->trainerRepository->toggleStatus($trainingTypeDetail);

    }

    public function getTrainerByType($type)
    {
        return $this->trainerRepository->findByType($type);
    }

}
