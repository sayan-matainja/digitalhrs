<?php

namespace App\Services\LoanManagement;

use App\Repositories\LoanTypeRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class LoanTypeService
{
    public function __construct(
        private LoanTypeRepository $loanTypeRepo
    ){}

    public function getAllLoanTypes($filterParameters,$select= ['*'],$with=[])
    {
        return $this->loanTypeRepo->getAllLoanTypes($filterParameters,$select,$with);
    }

    public function getAllActiveLoanTypes($select= ['*'],$with=[])
    {
        return $this->loanTypeRepo->getAllActiveLoanTypes($select,$with);
    }
    public function getBranchLoanTypes($branchId, $select= ['*'])
    {
        return $this->loanTypeRepo->getBranchLoanTypes($branchId, $select);
    }

    public function findLoanTypeById($id,$select=['*'],$with=[])
    {
        try{
            $LoanType =  $this->loanTypeRepo->findLoanTypeById($id,$select,$with);
            if(!$LoanType){
                throw new \Exception(__('message.Loan_type_not_found'),400);
            }
            return $LoanType;
        }catch(Exception $exception){
            throw $exception;
        }
    }

    public function store($validatedData)
    {
        try {
            DB::beginTransaction();
            $loanTypeDetail = $this->loanTypeRepo->create($validatedData);
            DB::commit();
            return $loanTypeDetail;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateLoanType($id, $validatedData)
    {
        try {
            $loanTypeDetail = $this->findLoanTypeById($id);
            DB::beginTransaction();
            $updateStatus = $this->loanTypeRepo->update($loanTypeDetail, $validatedData);
            DB::commit();
            return $updateStatus;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function deleteLoanType($id): bool
    {

        $loanTypeDetail = $this->findLoanTypeById($id);

        $this->loanTypeRepo->delete($loanTypeDetail);

        return true;

    }

    public function toggleIsActiveStatus($id): bool
    {
        try {
            DB::beginTransaction();
            $loanTypeDetail = $this->findLoanTypeById($id);
            $this->loanTypeRepo->toggleIsActiveStatus($loanTypeDetail);
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

}
