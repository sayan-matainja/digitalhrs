<?php

namespace App\Services\Payroll;

use App\Repositories\PaymentMethodRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentMethodService
{
    public function __construct(public PaymentMethodRepository $paymentMethodRepo){}

    public function getAllPaymentMethodList($select=['*'],$with=[])
    {
        try{
            return $this->paymentMethodRepo->getAllPaymentMethodLists($select,$with);
        }catch(\Exception $exception){
            throw $exception;
        }
    }

    public function store($validatedData)
    {
        try{
            $createdBy = getAuthUserCode();
            DB::beginTransaction();
                $paymentMethodData = [];
                foreach($validatedData['name'] as $key => $value){
                    $paymentMethodData[$key]['name'] = $value;
                    $paymentMethodData[$key]['slug'] = Str::slug($value);
                    $paymentMethodData[$key]['created_by'] = $createdBy;
                }
                $paymentMethod = $this->paymentMethodRepo->store($paymentMethodData);
            DB::commit();
            return $paymentMethod;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function findPaymentMethodById($id,$select=['*'])
    {
        try{
            return $this->paymentMethodRepo->findDetailById($id,$select);
        }catch(\Exception $exception){
            throw $exception;
        }
    }

    public function updateDetail($paymentMethodDetail,$validatedData)
    {
        try{
            DB::beginTransaction();
            $update = $this->paymentMethodRepo->update($paymentMethodDetail,$validatedData);
            DB::commit();;
            return $update;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function pluckAllActivePaymentMethod($select)
    {
        try{
            return $this->paymentMethodRepo->pluckAllPaymentMethodLists($select);
        }catch(\Exception $exception){
            throw $exception;
        }
    }

    public function deletePaymentMethodDetail($paymentMethodDetail)
    {
        try{
            DB::beginTransaction();
            $delete = $this->paymentMethodRepo->delete($paymentMethodDetail);
            DB::commit();;
            return $delete;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function changePaymentMethodStatus($paymentMethodDetail)
    {
        try{
            DB::beginTransaction();
            $status = $this->paymentMethodRepo->toggleStatus($paymentMethodDetail);
            DB::commit();;
            return $status;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
