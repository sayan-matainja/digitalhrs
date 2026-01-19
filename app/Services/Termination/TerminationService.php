<?php

namespace App\Services\Termination;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Repositories\TerminationRepository;
use App\Services\Notification\NotificationService;
use Exception;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;

class TerminationService
{
    public function __construct(
        protected TerminationRepository $terminationRepository, protected NotificationService $notificationService
    ){}

    public function getAllTerminationPaginated($filterParameters,$select= ['*'],$with=[])
    {
        if(AppHelper::ifDateInBsEnabled()){
            $filterParameters['termination_date'] = isset($filterParameters['termination_date']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['termination_date']): null;

        }
        return $this->terminationRepository->getAllTerminationPaginated($filterParameters,$select,$with);
    }



    /**
     * @throws Exception
     */
    public function findTerminationById($id, $select=['*'], $with=[])
    {
        return $this->terminationRepository->find($id,$select,$with);
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function saveTerminationDetail($validatedData)
    {
        return $this->terminationRepository->store($validatedData);

    }

    /**
     * @param $id
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function updateTerminationDetail($id, $validatedData)
    {
        $terminationDetail = $this->findTerminationById($id);
        return $this->terminationRepository->update($terminationDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function deleteTermination($id)
    {
        $terminationDetail = $this->findTerminationById($id);
        return $this->terminationRepository->delete($terminationDetail);
    }

    /**
     * @param $terminationId
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function updateStatus($terminationId, $validatedData){

        $terminationDetail =$this->findTerminationById($terminationId);
        $this->terminationRepository->update($terminationDetail, $validatedData);

        return $terminationDetail;
    }



    /**
     * @param $notificationData
     * @param $userId
     * @return void
     * @throws FirebaseException
     * @throws MessagingException
     */
    private function sendTerminationStatusNotification($notificationData, $userId): void
    {
        SMPushHelper::sendTerminationStatusNotification($notificationData->title, $notificationData->description,$userId);
    }


}
