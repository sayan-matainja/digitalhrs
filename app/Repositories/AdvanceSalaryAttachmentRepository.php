<?php

namespace App\Repositories;

use App\Models\AdvanceSalaryAttachment;
use App\Traits\ImageService;
use Exception;

class AdvanceSalaryAttachmentRepository
{
    use ImageService;

    public function findAdvanceSalaryAttachmentById($id, $with = [], $select = ['*'])
    {
        return AdvanceSalaryAttachment::select($select)->with($with)
            ->where('id', $id)
            ->first();
    }

    public function delete($attachmentDetail)
    {
        return $attachmentDetail->delete();
    }

    public function prepareAttachmentData($validatedAttachmentDetail): array
    {
        try {
            $attachments = [];
            foreach ($validatedAttachmentDetail as $key => $value) {
                $attachments[$key]['attachment'] = $this->storeImage($value, AdvanceSalaryAttachment::UPLOAD_PATH);
            }
            return $attachments;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function saveAdvanceSalaryAttachment(AdvanceSalaryAttachment $tadaDetail, $attachmentDetail)
    {
        return $tadaDetail->attachments()->createMany($attachmentDetail);
    }

    public function removeImageFromPublic($attachment)
    {
        $this->removeImage(AdvanceSalaryAttachment::UPLOAD_PATH, $attachment);
    }

}
