<?php

namespace App\Services\Tada;

use App\Models\TadaAttachment;
use App\Repositories\TadaRepository;
use App\Traits\ImageService;
use Exception;
use Illuminate\Support\Facades\DB;

class TadaService
{
    use ImageService;

    public TadaRepository $tadaRepo;

    public function __construct(TadaRepository $tadaRepo)
    {
        $this->tadaRepo = $tadaRepo;
    }

    public function getAllTadaDetailPaginated($filterParameters,$select=['*'],$with=[])
    {
        return $this->tadaRepo->getAllTadaPaginated($filterParameters,$select,$with);
    }

    public function getAllActiveTada($select=['*'],$with=[])
    {
        return $this->tadaRepo->getAllActiveTadaDetail($select,$with);
    }

    /**
     * @throws Exception
     */
    public function findTadaDetailById($id, $with=[], $select=['*'])
    {
        return $this->tadaRepo->findTadaDetailById($id,$select,$with);
    }

    /**
     * @throws Exception
     */
    public function findEmployeeTadaDetailByTadaId($id, $with=[], $select=['*'])
    {
        return $this->tadaRepo->findEmployeeTadaDetailByTadaId($id,$select,$with);
    }

    public function getAllTadaDetailOfEmployee($employeeId,$select=['*'],$with=[])
    {
        return $this->tadaRepo->getEmployeeTadaDetailLists($employeeId,$select,$with);
    }

    /**
     * @throws Exception
     */
    public function store($validatedData)
    {

        $attachments = $this->prepareAttachmentDataToStore($validatedData);

            $tada = $this->tadaRepo->store($validatedData);
            if($tada){
                $this->tadaRepo->createManyAttachment($tada,$attachments);
            }

        return $tada;

    }

    public function update($tadaDetail,$validatedData)
    {
        DB::beginTransaction();
//            if(isset($tadaDetail['attachments']) && count($tadaDetail['attachments']) > 0){
//                $this->tadaRepo->deleteTadaAttachments($tadaDetail);
//                $this->removeTadaOldAttachment($tadaDetail['attachments']);
//            }
        $tada = $this->tadaRepo->update($tadaDetail,$validatedData);
        if(isset($validatedData['attachments'])){
            $attachmentData = $this->prepareAttachmentDataToStore($validatedData);
            $this->tadaRepo->createManyAttachment($tadaDetail,$attachmentData);
        }
        DB::commit();
        return $tada;
    }

    /**
     * @throws Exception
     */
    public function delete($id)
    {
        try{
            $with=['attachments'];
            $tadaDetail = $this->findTadaDetailById($id,$with);
            DB::beginTransaction();
            $status = $this->tadaRepo->delete($tadaDetail);
            if($status && !is_null($tadaDetail->attachment)){
                $this->removeTadaOldAttachment($tadaDetail['attachment']);
            }
            DB::commit();
            return $status;
        }catch(Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    public function toggleStatus($id)
    {
        try{
            $detail = $this->findTadaDetailById($id);
            return $this->tadaRepo->toggleStatus($detail);
        }catch(Exception $exception){
            throw $exception;
        }
    }

    private function prepareAttachmentDataToStore($validatedData): array
    {
        try{
            $attachments = [];
            foreach ($validatedData['attachments'] as $key => $value){
                $attachments[$key]['attachment'] = $this->storeImage($value,TadaAttachment::ATTACHMENT_UPLOAD_PATH);
            }
            return $attachments;
        }catch(\Exception $exception){
            throw $exception;
        }
    }

    public function removeTadaOldAttachment($attachments)
    {
        try{
            foreach ($attachments as $key => $value){
                $this->removeImage(TadaAttachment::ATTACHMENT_UPLOAD_PATH, $value['attachment']);
            }
        }catch(\Exception $exception){
            throw $exception;
        }
    }

    public function changeTadaStatus($tadaDetail,$validatedData)
    {
        return $this->tadaRepo->changeTadaStatus($tadaDetail,$validatedData);
    }

    public function updateIsSettledStatus($tadaDetail)
    {
        return $this->tadaRepo->updateIsSettledStatus($tadaDetail);
    }

    /**
     * @throws Exception
     */
    public function makeSettlement($updateData, $employeePaySlipDetail)
    {
        return $this->tadaRepo->settleTada($updateData, $employeePaySlipDetail);

    }


}
