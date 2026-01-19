<?php

namespace App\Repositories;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use App\Traits\ImageService;

class AttachmentRepository
{
    use ImageService;

    public function findAttachmentDetailById($id,$select=['*'],$with=[])
    {
        return Attachment::select($select)->with($with)->where('id', $id)->first();
    }

    public function saveTaskAttachment(Task $taskDetail, $attachmentDetail)
    {
        return $taskDetail->taskAttachments()->createMany($attachmentDetail);
    }

    public function saveProjectAttachment(Project $projectDetail, $attachmentDetail)
    {
        return $projectDetail->projectAttachments()->createMany($attachmentDetail);
    }

    public function deleteAttachment($attachmentDetail)
    {
        $this->removeImage(Attachment::UPLOAD_PATH, $attachmentDetail->attachment);
        return $attachmentDetail->delete();
    }


    public function prepareAttachmentData($validatedAttachmentDetail): array
    {
        try{
            $attachments = [];
            foreach ($validatedAttachmentDetail as $key => $value){
                $attachments[$key]['attachment'] = $this->storeImage($value,Attachment::UPLOAD_PATH);
                $attachments[$key]['attachment_extension'] = $value->getClientOriginalExtension();
            }
            return $attachments;
        }catch(\Exception $exception){
            throw $exception;
        }
    }

    public function removeOldAttachments($attachment)
    {
        try{
            foreach ($attachment as $key => $value){
                $this->removeImage(Attachment::UPLOAD_PATH, $value['attachment']);
            }
        }catch(\Exception $exception){
            throw $exception;
        }
    }

}
