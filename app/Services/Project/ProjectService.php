<?php

namespace App\Services\Project;

use App\Helpers\AppHelper;
use App\Models\Project;
use App\Repositories\AttachmentRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;
use App\Traits\ImageService;
use Exception;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    use ImageService;


    public function __construct(protected ProjectRepository $projectRepo, protected AttachmentRepository $attachmentRepo, protected TaskRepository $taskRepository)
    {}

    public function getAllFilteredProjectsPaginated($filterParameter,$select = ['*'], $with = [])
    {
        return $this->projectRepo->getAllFilteredProjects($filterParameter, $select, $with);
    }

    public function getAllActiveProjects($select=['*'],$with=[])
    {
        return $this->projectRepo->getAllActiveProject($select, $with);
    }

    public function getActiveBranchProject($branchId,$select=['*'])
    {
        return $this->projectRepo->getBranchProjects($branchId, $select);
    }

    public function findProjectDetailById($id,$with=[],$select = ['*'])
    {
        return $this->projectRepo->findProjectDetailById($id,$with,$select);
    }

    public function getAllProjectLists($select=['*'])
    {
        return $this->projectRepo->getAllProjectLists($select);
    }

    public function getProjectCardData()
    {
        $projectDetail =  $this->projectRepo->getAllProjectDetailForDashboardCard();
        $data = [
            'not_started' => $projectDetail['not_started'] ?? 0,
            'on_hold' => $projectDetail['on_hold'] ?? 0,
            'in_progress' => $projectDetail['in_progress'] ?? 0,
            'completed' => $projectDetail['completed'] ?? 0,
            'cancelled' => $projectDetail['cancelled'] ?? 0
        ];
        $data['total_projects'] = array_sum($data);

        return $data;
    }

    public function getRecentProjectListsForDashboard($select=['*'],$with=[])
    {
        return $this->projectRepo->getRecentProjectListsForDashboard($select,$with);
    }

    /**
     * @throws Exception
     */
    public function findAssignedMemberProjectDetailById($projectId, $with=[], $select = ['*'])
    {
        return $this->projectRepo->findAssignedMemberProjectDetailById($projectId,$with,$select);

    }

    public function getAllActiveProjectsOfEmployee($employeeId,$select=['*'],$with=[])
    {
        return $this->projectRepo->getAllActiveProjectsOfEmployee($employeeId,$select,$with);
    }

    public function getAllActiveProjectOfEmployeePaginated($employeeId,$perPage,$select=['*'],$with=[])
    {
        return $this->projectRepo->getAllActiveProjectsOfEmployeePaginated($employeeId,$perPage,$select,$with);
    }
    /**
     * @throws \Exception
     */
    public function saveProjectDetail($validatedData)
    {
        try{
            $leaderMemberData = $this->getLeaderAndMemberData($validatedData);
            DB::beginTransaction();
            $validatedData['cover_pic'] = $this->storeImage($validatedData['cover_pic'],Project::UPLOAD_PATH);
            $projectDetail = $this->projectRepo->store($validatedData);
            $this->projectRepo->saveProjectTeamLeader($projectDetail,$leaderMemberData['leader']);
            $this->projectRepo->assignMemberToProject($projectDetail,$leaderMemberData['member']);
            if(isset($validatedData['attachments'])){
                $projectFiles = $this->attachmentRepo->prepareAttachmentData($validatedData['attachments']);
                $this->attachmentRepo->saveProjectAttachment($projectDetail,$projectFiles);
            }
            DB::commit();
            return $projectDetail;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
    /**
     * @throws \Exception
     */
    public function updateProjectDetail($validatedData, $projectId)
    {
        try {
            $with = ['projectAttachments'];
            $projectDetail = $this->findProjectDetailById($projectId,$with);

            DB::beginTransaction();
            if(isset($validatedData['cover_pic'])){
                $this->removeImage(Project::UPLOAD_PATH, $projectDetail['cover_pic']);
                $validatedData['cover_pic'] = $this->storeImage($validatedData['cover_pic'],Project::UPLOAD_PATH);
            }
            $updateStatus = $this->projectRepo->update($projectDetail, $validatedData);
            if(!$updateStatus){
                throw new \Exception(__('message.something_went_wrong'),400);
            }

            if(isset($validatedData['attachments'])){
                $projectFiles = $this->attachmentRepo->prepareAttachmentData($validatedData['attachments']);
                $this->attachmentRepo->saveProjectAttachment($projectDetail,$projectFiles);
            }
            $leaderMemberData = $this->getLeaderAndMemberData($validatedData);
            $this->projectRepo->saveProjectTeamLeader($projectDetail,$leaderMemberData['leader']);
            $this->projectRepo->assignMemberToProject($projectDetail,$leaderMemberData['member']);

            DB::commit();
            return $projectDetail;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    private function getLeaderAndMemberData($validatedData): array
    {
        try{
            $teamLeaderArray = [];
            $assignedMemberArray = [];
            foreach ($validatedData['project_leader'] as $key => $leader){
                $teamLeaderArray[$key]['leader_id'] = $leader;
            }
            foreach ($validatedData['assigned_member'] as $key => $value){
                $assignedMemberArray[$key]['member_id'] = $value;
            }
            $data['leader'] = $teamLeaderArray;
            $data['member'] = $assignedMemberArray;
            return $data;
        }catch(\Exception $exception){
            throw $exception;
        }
    }

    /**
     * @param $id
     * @return bool
     * @throws Exception
     */
    public function toggleStatus($id): bool
    {

        $projectDetail = $this->findProjectDetailById($id);
        if ($projectDetail->is_active == 1) {
            $changeStatus = 0;
        }else{
            $changeStatus = 1;
        }
        $this->taskRepository->toggleTaskByProjectId($id, $changeStatus);

        return $this->projectRepo->toggleStatus($projectDetail);

    }
    /**
     * @throws Exception
     */
    public function deleteProjectDetail($id): bool
    {
        try{
            $projectDetail = $this->findProjectDetailById($id);

            DB::beginTransaction();
            if(count($projectDetail->projectAttachments) > 0){
                $this->attachmentRepo->removeOldAttachments($projectDetail->projectAttachments);
            }
            $status  = $this->projectRepo->delete($projectDetail);
            DB::commit();
            return $status;
        }catch(\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    public function updateProjectProgressStatus($projectDetail)
    {
        $completedStatus = 'completed';
        $notCompletedStatus = 'in_progress';
        $projectProgress = $projectDetail->getProjectProgressInPercentage();
        $projectStatus = (intval($projectProgress) == 100) ? $completedStatus : $notCompletedStatus;
        return $this->projectRepo->changeProjectProgressStatus($projectDetail,$projectStatus);

    }

    /**
     * @throws Exception
     */
    public function getAllLeaderDetailAssignedInProject($projectId)
    {
        return $this->projectRepo->getAllLeaderDetailAssignedInProject($projectId);
    }

    /**
     * @throws Exception
     */
    public function getAllMemberDetailAssignedInProject($projectId)
    {
        return $this->projectRepo->getAllMemberDetailAssignedInProject($projectId);
    }

    /**
     * @throws Exception
     */
    public function updateLeadersOfProject($projectDetail, $validatedData)
    {
        $assignedMemberArray = [];
        foreach ($validatedData['employee'] as $value) {
            $assignedMemberArray[] = ['leader_id' => $value];
        }
        return $this->projectRepo->updateProjectLeader($projectDetail,$assignedMemberArray);
    }

    /**
     * @throws Exception
     */
    public function updateMemberOfProject($projectDetail, $validatedData)
    {
        $assignedMemberArray = [];
        foreach ($validatedData['employee'] as $value) {
            $assignedMemberArray[] = ['member_id' => $value];
        }
        return $this->projectRepo->updateProjectMember($projectDetail,$assignedMemberArray);
    }


    public function checkClient($clientId)
    {
        return $this->projectRepo->checkProjectClient($clientId);

    }

}
