<?php

namespace App\Services\Attachment;

use App\Repositories\AttachmentRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;
use App\Traits\ImageService;
use Illuminate\Support\Facades\DB;

class AttachmentService
{
    use ImageService;

    public AttachmentRepository $attachmentRepo;
    public ProjectRepository $projectRepo;
    public TaskRepository $taskRepo;

    public function __construct(AttachmentRepository $attachmentRepo,
                                ProjectRepository $projectRepo,
                                TaskRepository $taskRepo
    )
    {
        $this->attachmentRepo = $attachmentRepo;
        $this->projectRepo = $projectRepo;
        $this->taskRepo = $taskRepo;
    }

    public function findAttachmentById($id,$select=['*'],$with=[])
    {
        try{
            $attachment = $this->attachmentRepo->findAttachmentDetailById($id,$select,$with);
            if(!$attachment){
                throw new \Exception(__('message.attachment_not_found'),404);
            }
            return $attachment;
        }catch(\Exception $e){
            throw $e;
        }
    }

    public function storeProjectAttachment($validatedData)
    {
        try{
            DB::beginTransaction();
            $projectDetail = $this->projectRepo->findProjectDetailById($validatedData['project_id'],$with=[],$select=['*']);
//                $reflection = new ReflectionClass($projectDetail);
//                $className = $reflection->getShortName();
            $projectAttachments = $this->attachmentRepo->prepareAttachmentData($validatedData['attachments']);
            $status = $this->attachmentRepo->saveProjectAttachment($projectDetail,$projectAttachments);
            DB::commit();
            return $status;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function storeTaskAttachment($validatedData)
    {
        try{
            DB::beginTransaction();
            $taskDetail = $this->taskRepo->findTaskDetailById($validatedData['task_id'],$with=[],$select=['*']);
            $taskAttachments = $this->attachmentRepo->prepareAttachmentData($validatedData['attachments']);
            $status = $this->attachmentRepo->saveTaskAttachment($taskDetail,$taskAttachments);
            DB::commit();
            return $status;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }


    public function deleteProjectAttachment($id)
    {
        try{
            $attachmentDetail = $this->findAttachmentById($id);
            if(!$attachmentDetail){
                throw new \Exception(__('message.project_attachment_not_found'),404);
            }
            DB::beginTransaction();
                $status = $this->attachmentRepo->deleteAttachment($attachmentDetail);
            DB::commit();
            return $status;
        }catch(\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

}
