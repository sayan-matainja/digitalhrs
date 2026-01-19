<?php

namespace App\Services\Payroll;

use App\Models\AdvanceSalaryAttachment;
use App\Repositories\AdvanceSalaryAttachmentRepository;
use App\Repositories\AdvanceSalaryRepository;
use App\Traits\ImageService;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdvanceSalaryService
{
    use ImageService;

    public function __construct(public AdvanceSalaryRepository $advanceSalaryRepo, public AdvanceSalaryAttachmentRepository $advanceSalaryAttachmentRepo){}

    public function getAllAdvanceSalaryDetailPaginated($filterParameters,$select=['*'],$with=[])
    {
        return $this->advanceSalaryRepo->getAllAdvanceSalaryRequestLists($filterParameters,$select,$with);
    }

    public function getAllEmployeeAdvanceSalaryListDetail($employeeId,$select=['*'],$with=[])
    {
        return $this->advanceSalaryRepo->getAllEmployeeAdvanceSalaryRequestLists($employeeId,$select,$with);
    }

    /**
     * @throws Exception
     */
    public function findEmployeeAdvanceSalaryDetailByIdAndEmployeeId($id, $with=[], $select=['*'])
    {
        try{
            $detail = $this->advanceSalaryRepo->findEmployeeAdvanceSalaryDetailByIdAndEmployeeId($id,$select,$with);
            if(!$detail){
                throw new Exception(__('message.advance_salary_not_found'),404);
            }
            return $detail;
        }catch(Exception $ex){
            throw $ex;
        }
    }

    public function findAdvanceSalaryDetailById($id, $with=[], $select=['*'])
    {
        try{
            $detail = $this->advanceSalaryRepo->findAdvanceSalaryDetailById($id,$select,$with);
            if(!$detail){
                throw new Exception(__('message.advance_salary_not_found'),404);
            }
            return $detail;
        }catch(Exception $ex){
            throw $ex;
        }
    }

    public function checkIfEmployeeUnsettledAdvanceSalaryRequestExists($employeeId)
    {
        return $this->advanceSalaryRepo->checkIfEmployeeUnsettledAdvanceSalaryRequestExists($employeeId);
    }

    /**
     * @throws Exception
     */
    public function store($validatedData)
    {
        try{
            $validatedData['employee_id'] = getAuthUserCode();
            $validatedData['advance_requested_date'] = Carbon::now()->format('Y-m-d h:i:s');
            DB::beginTransaction();
                $advanceSalary = $this->advanceSalaryRepo->store($validatedData);
                if($advanceSalary && isset($validatedData['documents'])){
                    $attachments = $this->prepareAttachmentDataToStore($validatedData['documents']);
                    $this->advanceSalaryRepo->createManyAttachment($advanceSalary,$attachments);
                }
            DB::commit();
            return $advanceSalary;
        }catch(Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @throws Exception
     */
    public function changeAdvanceSalaryStatus($advanceSalaryDetail, $validatedData)
    {
        return $this->advanceSalaryRepo->changeAdvanceSalaryStatus($advanceSalaryDetail,$validatedData);
    }

    public function  update($advanceSalaryDetail,$validatedData)
    {
        try{
            DB::beginTransaction();
            $advanceSalary = $this->advanceSalaryRepo->update($advanceSalaryDetail,$validatedData);
            DB::commit();
            return $advanceSalary;
        }catch(Exception $exception){
            throw $exception;
        }
    }

    /**
     * @throws Exception
     */
    public function advanceSalaryUpdateByAdmin($advanceSalaryDetail, $validatedData)
    {
        if (in_array($advanceSalaryDetail->status, ['approved', 'rejected'])) {
            throw new Exception(__('message.advance_salary_update_error'), 400);
        }

        if ($advanceSalaryDetail->requested_amount < $validatedData['released_amount']) {
            throw new Exception(__('message.advance_salary_limit',['amount'=>$advanceSalaryDetail->requested_amount])  , 400);
        }

        if ($validatedData['status'] == 'approved') {
            $validatedData['amount_granted_date'] = Carbon::now()->format('Y-m-d h:i:s');
        }

        if ($validatedData['status'] == 'rejected') {
            $validatedData['is_settled'] = true;
        }
        DB::beginTransaction();
        $advanceSalary = $this->advanceSalaryRepo->update($advanceSalaryDetail,$validatedData);
        if(isset($validatedData['documents'])){
            $attachmentData = $this->prepareAttachmentDataToStore($validatedData['documents']);
            $this->advanceSalaryRepo->createManyAttachment($advanceSalaryDetail,$attachmentData);
        }
        DB::commit();
        return $advanceSalary;
    }

    /**
     * @throws Exception
     */
    public function delete($id)
    {
        try{
            $select = ['*'];
            $with=['attachments'];
            $advanceSalaryDetail = $this->advanceSalaryRepo->findAdvanceSalaryDetailById($id,$select,$with);
            if(!$advanceSalaryDetail){
                throw new Exception(__('message.advance_salary_not_found'),404);
            }
            if($advanceSalaryDetail->status == 'approved'){
                throw new Exception(__('message.approve_salary_delete_error'),400);
            }
            DB::beginTransaction();
            $status = $this->advanceSalaryRepo->delete($advanceSalaryDetail);
            if($status && !is_null($advanceSalaryDetail->attachment)){
                $this->removeAdvanceSalaryOldAttachment($advanceSalaryDetail['attachment']);
            }
            DB::commit();
            return $status;
        }catch(Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    private function prepareAttachmentDataToStore($documents): array
    {
        $attachments = [];
        foreach ($documents as $key => $value){
            $attachments[$key]['name'] = $this->storeImage($value,AdvanceSalaryAttachment::UPLOAD_PATH);
        }
        return $attachments;
    }

    /**
     * @throws Exception
     */
    public function removeAdvanceSalaryOldAttachment($documents)
    {
        foreach ($documents as $key => $value){
            $this->removeImage(AdvanceSalaryAttachment::UPLOAD_PATH, $value['name']);
        }
    }

    public function advanceSalarySettlement($employeePaySlipDetail, $updateData)
    {
       return $this->advanceSalaryRepo->settlement($employeePaySlipDetail, $updateData);
    }

    public function getEmployeeApprovedAdvanceSalaries($employeeId,$firstDay, $days)
    {
        return $this->advanceSalaryRepo->getEmployeeApprovedAdvanceSalaryList($employeeId,$firstDay,$days);
    }


}
