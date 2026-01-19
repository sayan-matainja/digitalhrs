<?php

namespace App\Repositories;

use App\Models\TeamMeeting;
use App\Traits\ImageService;
use Illuminate\Support\Carbon;

class TeamMeetingRepository
{
    use ImageService;

    public function getAllCompanyTeamMeetings($filterParameters,$select=['*'],$with=[])
    {
        $branchId = null;
        $authUserId = null;
        if(auth()->user()){
            $branchId = auth()->user()->branch_id;
            $authUserId = auth()->user()->id;
        }

        return TeamMeeting::select($select)->with($with)

            ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('branch_id',$filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['department_id']), function($query) use ($filterParameters){
                $query->whereHas('meetingDepartment',function($query) use ($filterParameters){
                    $query->whereIn('department_id', $filterParameters['department_id'] );
                });

            })
            ->when(isset($filterParameters['participator']), function($query) use ($filterParameters){
                $query->whereHas('teamMeetingParticipator',function($query) use ($filterParameters){
                    $query->whereIn('meeting_participator_id', $filterParameters['participator'] );
                });

            })
            ->when(isset($filterParameters['meeting_from']) || isset($filterParameters['meeting_to']), function($query) use ($filterParameters){
                if(isset($filterParameters['meeting_from'])){
                    $query->whereDate('meeting_date','>=',date('Y-m-d',strtotime($filterParameters['meeting_from'])));
                }
                if(isset($filterParameters['meeting_to'])){
                    $query->whereDate('meeting_date','<=',date('Y-m-d',strtotime($filterParameters['meeting_to'])));
                }
            })
            ->where('company_id',$filterParameters['company_id'])
            ->when(isset($branchId), function ($query) use ($branchId) {
                $query->whereHas('branch',function($subQuery) use ($branchId){
                    $subQuery->where('id', $branchId);
                });

            })
            ->orderBy('meeting_published_at','Desc')
            ->paginate( getRecordPerPage());
    }

    public function getAllAssignedEmployeeTeamMeetings($perPage,$select=['*'])
    {
        return TeamMeeting::select($select)
            ->whereHas('teamMeetingParticipator',function($query){
                $query->where('meeting_participator_id',getAuthUserCode());
            })

            ->where('meeting_published_at','>=',Carbon::now()->subMonth(12))
            ->orderBy('meeting_published_at','Desc')
            ->paginate($perPage);
    }

    public function getAssignedMonthTeamMeetingDetail($filterParameter,$select=['*'])
    {
        return TeamMeeting::select($select)
            ->whereHas('teamMeetingParticipator',function($query){
                $query->where('meeting_participator_id',getAuthUserCode());
            })

            ->where(function ($query) use ($filterParameter) {
                $query->whereBetween('meeting_date', [$filterParameter['start_date'], $filterParameter['end_date']]);
            })
            ->get();
    }

    public function findTeamMeetingDetailById($id,$select=['*'],$with=[])
    {
        return TeamMeeting::select($select)->with($with)->where('id',$id)->first();
    }

    public function store($validatedData)
    {
        if(isset($validatedData['image'])){
            $validatedData['image'] = $this->storeImage($validatedData['image'], TeamMeeting::UPLOAD_PATH);
        }
        return TeamMeeting::create($validatedData)->fresh();
    }

    public function update($teamMeetingDetail,$validatedData)
    {
        if (isset($validatedData['image'])) {
            $this->removeImage(TeamMeeting::UPLOAD_PATH, $teamMeetingDetail['image']);
            $validatedData['image'] = $this->storeImage($validatedData['image'], TeamMeeting::UPLOAD_PATH);
        }
        $teamMeetingDetail->update($validatedData);
        return $teamMeetingDetail;
    }

    public function delete($teamMeetingDetail)
    {
        if($teamMeetingDetail['image']){
            $this->removeImage(TeamMeeting::UPLOAD_PATH, $teamMeetingDetail['image']);
        }
        return $teamMeetingDetail->delete();
    }

    public function deleteMeetingImage($teamMeetingDetail)
    {
        if (isset($teamMeetingDetail['image'])) {
            $this->removeImage(TeamMeeting::UPLOAD_PATH, $teamMeetingDetail['image']);
        }
        return $teamMeetingDetail->update(['image' => null ]);
    }

    public function createManyTeamMeetingParticipator(TeamMeeting $teamMeetingDetail,$validatedData)
    {
        return $teamMeetingDetail->teamMeetingParticipator()->createMany($validatedData);
    }

    public function deleteTeamMeetingParticipatorsDetail($teamMeetingDetail)
    {
        $teamMeetingDetail->teamMeetingParticipator()->delete();
        return true;
    }

    public function createManyTeamMeetingDepartment(TeamMeeting $teamMeetingDetail,$validatedData)
    {
        return $teamMeetingDetail->meetingDepartment()->createMany($validatedData);
    }

    public function deleteTeamMeetingDepartmentsDetail($teamMeetingDetail)
    {
        $teamMeetingDetail->meetingDepartment()->delete();
        return true;
    }

}
