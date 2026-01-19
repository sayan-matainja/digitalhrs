<?php

namespace App\Services\TeamMeeting;

use App\Helpers\AppHelper;
use App\Repositories\TeamMeetingRepository;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class TeamMeetingService
{
    private TeamMeetingRepository $teamMeetingRepo;

    public function __construct(TeamMeetingRepository $teamMeetingRepo)
    {
        $this->teamMeetingRepo = $teamMeetingRepo;
    }

    public function getAllCompanyTeamMeetings($filterParameters, $select = ['*'], $with = [])
    {
        if(AppHelper::ifDateInBsEnabled()){
            $filterParameters['meeting_from'] = isset($filterParameters['meeting_from']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['meeting_from']): null;
            $filterParameters['meeting_to'] = isset($filterParameters['meeting_to']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['meeting_to']): null;
        }
        return $this->teamMeetingRepo->getAllCompanyTeamMeetings($filterParameters, $select, $with);
    }


    public function getAllAssignedTeamMeetingDetail($perPage)
    {
        return $this->teamMeetingRepo->getAllAssignedEmployeeTeamMeetings($perPage);
    }

    public function getAssignedMonthTeamMeetingDetail($filterParameter, $select=['*'])
    {
        return $this->teamMeetingRepo->getAssignedMonthTeamMeetingDetail($filterParameter, $select);
    }

    /**
     * @param $id
     * @param $select
     * @param $with
     * @return mixed
     * @throws Exception
     */
    public function findOrFailTeamMeetingDetailById($id, $select = ['*'], $with = [])
    {
        return $this->teamMeetingRepo->findTeamMeetingDetailById($id, $select, $with);
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function store($validatedData)
    {
        try {
            $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
            DB::beginTransaction();
            $teamMeeting = $this->teamMeetingRepo->store($validatedData);
            if ($teamMeeting) {
                $this->createManyTeamMeetingParticipator($teamMeeting, $validatedData);
                $this->createManyTeamMeetingDepartment($teamMeeting, $validatedData);

            }
            DB::commit();
            return $teamMeeting;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param $teamMeetingDetail
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function update($teamMeetingDetail, $validatedData)
    {


            $teamMeeting = $this->teamMeetingRepo->update($teamMeetingDetail, $validatedData);
            if ($teamMeeting) {
                $deleteParticipatorDetail = $this->teamMeetingRepo->deleteTeamMeetingParticipatorsDetail($teamMeeting);
                if ($deleteParticipatorDetail) {
                    $this->createManyTeamMeetingParticipator($teamMeeting, $validatedData);
                }
                $deleteDepartmentDetail = $this->teamMeetingRepo->deleteTeamMeetingDepartmentsDetail($teamMeeting);
                if ($deleteDepartmentDetail) {
                    $this->createManyTeamMeetingDepartment($teamMeeting, $validatedData);
                }
            }

            return $teamMeeting;

    }

    /**
     * @param $id
     * @return void
     * @throws Exception
     */

    public function deleteTeamMeeting($id)
    {
        try {
            DB::beginTransaction();
                $teamMeetingDetail = $this->findOrFailTeamMeetingDetailById($id);
                $this->teamMeetingRepo->delete($teamMeetingDetail);
            DB::commit();
            return;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function removeMeetingImage($id)
    {
        try {
            DB::beginTransaction();
            $teamMeetingDetail = $this->findOrFailTeamMeetingDetailById($id);
            if($teamMeetingDetail->image){
                $this->teamMeetingRepo->deleteMeetingImage($teamMeetingDetail);
            }
            DB::commit();
            return;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function createManyTeamMeetingParticipator($teamMeetingDetail, $validatedData)
    {

        $this->teamMeetingRepo->createManyTeamMeetingParticipator($teamMeetingDetail,$validatedData['participator']);

    }
    public function createManyTeamMeetingDepartment($teamMeetingDetail, $validatedData)
    {

        $this->teamMeetingRepo->createManyTeamMeetingDepartment($teamMeetingDetail,$validatedData['department']);

    }

}

