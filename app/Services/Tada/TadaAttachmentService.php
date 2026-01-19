<?php

namespace App\Services\Tada;

use App\Repositories\TadaAttachmentRepository;
use App\Repositories\TadaRepository;
use Exception;

class TadaAttachmentService
{
    public TadaAttachmentRepository $attachmentRepo;
    public TadaRepository $tadaRepo;

    public function __construct(TadaAttachmentRepository $attachmentRepo, TadaRepository $tadaRepo)
    {
        $this->attachmentRepo = $attachmentRepo;
        $this->tadaRepo = $tadaRepo;
    }
    /**
     * @throws Exception
     */
    public function store($validatedData)
    {
        $select = ['*'];
        $with = [];
        $tadaDetail = $this->tadaRepo->findTadaDetailById($validatedData['tada_id'], $select, $with);

        $tadaAttachment = $this->attachmentRepo->prepareAttachmentData($validatedData['attachments']);
        return $this->attachmentRepo->saveTadaAttachment($tadaDetail, $tadaAttachment);
    }
    /**
     * @throws Exception
     */
    public function delete($attachmentDetail)
    {
        $status = $this->attachmentRepo->delete($attachmentDetail);
        if ($status) {
            $this->attachmentRepo->removeImageFromPublic($attachmentDetail['attachment']);
        }
        return $status;
    }

    /**
     * @throws Exception
     */
    public function findTadaAttachmentById($id, $with = [], $select = ['*'])
    {
        return $this->attachmentRepo->findTadaAttachmentById($id);
    }

    /**
     * @throws Exception
     */
    public function findEmployeeTadaAttachmentDetail($attachmentId, $select, $with)
    {
        return $this->attachmentRepo->findAttachmentDetailByAttachmentIdAndEmployeeId(getAuthUserCode(),$attachmentId,$select,$with);
    }

}
