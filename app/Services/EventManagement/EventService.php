<?php

namespace App\Services\EventManagement;

use App\Helpers\AppHelper;
use App\Repositories\EventRepository;
use App\Repositories\TeamMeetingRepository;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class EventService
{

    public function __construct(protected EventRepository $eventRepository)
    {}

    public function getAllEvents($filterParameters,$select = ['*'], $with = [])
    {
        if(AppHelper::ifDateInBsEnabled()){
            $filterParameters['start_date'] = isset($filterParameters['start_date']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['start_date']): null;
            $filterParameters['end_date'] = isset($filterParameters['end_date']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['end_date']): null;

        }
        return $this->eventRepository->getAll($filterParameters,$select, $with);
    }

    public function getApiEvents($perPage,$select=['*'], $isUpcomingEvent =1)
    {
        $this->updateStatus();
        return $this->eventRepository->getApiEvents($perPage,$select, $isUpcomingEvent);
    }

    public function getMonthEvents($filterParameter,$select=['*'])
    {
        return $this->eventRepository->getMonthEvents($filterParameter,$select);
    }

    public function getActiveBackendEvents($perPage)
    {
        return $this->eventRepository->getActiveBackendEvents($perPage);
    }
    public function getPastBackendEvents($perPage)
    {
        return $this->eventRepository->getPastBackendEvents($perPage);
    }

    public function getRecentEvents($select=['*'])
    {
        return $this->eventRepository->getRecentEvent($select);
    }

    /**
     * @param $id
     * @param $select
     * @param $with
     * @return mixed
     * @throws Exception
     */
    public function findEventDetailById($id, $select = ['*'], $with = [])
    {
        return $this->eventRepository->find($id, $select, $with);
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function storeEvent($validatedData)
    {

        $relationData = $this->getEventRelationData($validatedData);

        $validatedData['created_by'] = auth()->user()->id ?? null;

        $eventDetail = $this->eventRepository->store($validatedData);

        if ($eventDetail) {
            $this->eventRepository->saveDepartment($eventDetail, $relationData['department']);
            $this->eventRepository->saveUser($eventDetail, $relationData['users']);
        }
        return $eventDetail;

    }

    /**
     * @param $eventId
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function update($eventId, $validatedData)
    {
        $relationData = $this->getEventRelationData($validatedData);
        $eventDetail = $this->findEventDetailById($eventId);
        $this->eventRepository->update($eventDetail, $validatedData);

        $this->eventRepository->updateDepartment($eventDetail, $relationData['department']);
        $this->eventRepository->updateUser($eventDetail, $relationData['users']);
        return $eventDetail;

    }

    /**
     * @param $id
     * @return void
     * @throws Exception
     */

    public function deleteEvent($id)
    {

        $eventDetail = $this->findEventDetailById($id);
        return $this->eventRepository->delete($eventDetail);

    }

    public function removeEventAttachment($id)
    {

        $eventDetail = $this->findEventDetailById($id);
        if($eventDetail->image){
            $this->eventRepository->deleteAttachment($eventDetail);
        }
        return $eventDetail;
    }

    private function getEventRelationData($validatedData): array
    {
        $departmentArray = [];
        $userArray = [];
        foreach ($validatedData['department_id'] as $key => $department){
            $departmentArray[$key]['department_id'] = $department;
        }

        foreach ($validatedData['employee_id'] as $key => $value){
            $userArray[$key]['user_id'] = $value;
        }

        $data['department'] = $departmentArray;
        $data['users'] = $userArray;
        return $data;
    }

    public function updateStatus()
    {
        $this->eventRepository->updateAllStatus();
    }

}

