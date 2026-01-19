<?php

namespace App\Repositories;

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectRepository
{
    const ASSIGNABLE_TYPE = 'project';
    public function getAllFilteredProjects($filterParameters,$select,$with): mixed
    {

        return Project::query()->select($select)->with($with)

        ->when(isset($filterParameters['project_name']), function ($query) use ($filterParameters) {
            $query->where('id', $filterParameters['project_name']);
        })
        ->when(isset($filterParameters['status']), function ($query) use ($filterParameters) {
            $query->where('status', $filterParameters['status']);
        })
        ->when(isset($filterParameters['priority']), function ($query) use ($filterParameters) {
            $query->where('priority', $filterParameters['priority']);
        })
        ->when(isset($filterParameters['branch_id']), function ($query) use ($filterParameters) {
            $query->where('branch_id', $filterParameters['branch_id']);
        })
        ->when(isset($filterParameters['members']), function ($query) use ($filterParameters) {
            $query->whereHas('assignedMembers.user',function($subQuery) use ($filterParameters){
                    $subQuery->whereIn('id', $filterParameters['members']);
                });
         })
            ->orderByRaw("
                CASE
                    WHEN status = 'in_progress' THEN 1
                    ELSE 2
                END ASC
            ")
        ->orderBy('deadline','desc')
        ->paginate( getRecordPerPage());
    }

    public function getAllActiveProject($select=['*'], $with=[])
    {
        return Project::select($select)->with($with)
            ->where('is_active',1)
            ->get();
    }

    public function getBranchProjects($branchId, $select=['*'])
    {
        return Project::select($select)
            ->where('is_active',1)
            ->where('branch_id',$branchId)
            ->get();
    }

    public function store($validatedData):mixed
    {
        $validatedData['slug'] = Str::slug($validatedData['name']).'-'.time();
        return Project::create($validatedData)->fresh();
    }

    public function toggleStatus($projectDetail):mixed
    {
        return $projectDetail->update([
            'is_active' => !$projectDetail->is_active,
        ]);

    }

    public function findProjectDetailById($id,$with,$select):mixed
    {
        return Project::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function getAllProjectLists($select)
    {
        return Project::select($select)->latest()->get();
    }

    public function getAllProjectDetailForDashboardCard()
    {
        return Project::selectRaw('status, count(*) as count')
            ->where('is_active', 1)
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function getRecentProjectListsForDashboard($select=['*'],$with=[])
    {
        return Project::query()
            ->select($select)
            ->with($with)
            ->latest()
            ->take(5)
            ->get();
    }

    public function findAssignedMemberProjectDetailById($projectId,$with,$select):mixed
    {
        return Project::select($select)
            ->with($with)
            ->where(function($query){
                $query->whereHas('assignedMembers',function($subQuery){
                    $subQuery->where('member_id', getAuthUserCode())
                    ->where('assignable_type','project');

                })
                ->orWhereHas('projectLeaders', function ($subQuery) {
                    $subQuery->where('leader_id', getAuthUserCode());
                });
            })
            ->where('id',$projectId)
            ->where('is_active',1)
            ->first();
    }

    public function getAllActiveProjectsOfEmployee($employeeId,$select,$with)
    {
        return Project::select($select)->with($with)
            ->where(function($query) use ($employeeId){
                $query->whereHas('assignedMembers',function($subQuery) use ($employeeId){
                    $subQuery->where('member_id', $employeeId)
                        ->where('assignable_type','project');
                })
                ->orWhereHas('projectLeaders', function ($subQuery) use ($employeeId) {
                    $subQuery->where('leader_id', $employeeId);
                });
            })
            ->where('is_active',1)
            ->get();
    }

    public function getAllActiveProjectsOfEmployeePaginated($employeeId,$perPage,$select,$with)
    {

        $paginate = $perPage;
        return Project::select($select)->with($with)
            ->where(function($query) use ($employeeId){
                $query->whereHas('assignedMembers',function($subQuery) use ($employeeId){
                    $subQuery->where('member_id', $employeeId);
                })
                ->orWhereHas('projectLeaders', function ($subQuery) use ($employeeId) {
                    $subQuery->where('leader_id', $employeeId);
                });
            })
            ->orderBy('deadline','desc')
            ->paginate($paginate);

    }

    public function update($projectDetail, $validatedData)
    {
        $projectDetail->update($validatedData);
        if(isset($validatedData['attachments'])){
            $projectDetail->projectAttachments()->delete();
        }
        $projectDetail->assignedMembers()->delete();
        return $projectDetail->projectLeaders()->delete();
    }

    public function changeProjectProgressStatus($projectDetail,$projectStatus)
    {
        return $projectDetail->update([
            'status' => $projectStatus
        ]);
    }

    public function delete(Project $projectDetail)
    {
        return $projectDetail->delete();
    }

    public function dropAssignedMembers($projectDetail)
    {
        $projectDetail->assignedMembers()->delete();
        return true;
    }

    public function assignMemberToProject(Project $projectDetail,$assignedMemberArray)
    {
        return $projectDetail->assignedMembers()->createMany($assignedMemberArray);
    }

    public function saveProjectTeamLeader(Project $projectDetail,$teamLeaderArray)
    {
        return $projectDetail->projectLeaders()->createMany($teamLeaderArray);
    }

    public function updateProjectLeader(Project $projectDetail,$teamLeaderArray)
    {
        $projectDetail->projectLeaders()->delete();
        return $projectDetail->projectLeaders()->createMany($teamLeaderArray);
    }

    public function updateProjectMember(Project $projectDetail,$teamMemberArray)
    {
        $projectDetail->assignedMembers()->delete();
        return $projectDetail->assignedMembers()->createMany($teamMemberArray);
    }

    public function getAllLeaderDetailAssignedInProject($projectId)
    {
        return DB::table('projects')
            ->select([
               'users.id as id',
            ])
            ->join('project_team_leaders','projects.id','project_team_leaders.project_id')
            ->join('users','users.id','project_team_leaders.leader_id')
            ->where('projects.id',$projectId)
            ->get();
    }

    public function getAllMemberDetailAssignedInProject($projectId)
    {

        return DB::table('projects')
            ->select([
                'users.id as id',
                'users.name as name',
            ])
            ->join('assigned_members','projects.id','assigned_members.assignable_id')
            ->join('users','users.id','assigned_members.member_id')
            ->where('projects.id',$projectId)
            ->where('assigned_members.assignable_type',self::ASSIGNABLE_TYPE)
            ->distinct()
            ->get();
    }

    public function checkProjectClient($clientId)
    {
        return Project::where('client_id',$clientId)
            ->count();
    }


}
