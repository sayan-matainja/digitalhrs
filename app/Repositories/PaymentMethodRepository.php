<?php

namespace App\Repositories;

use App\Models\PaymentMethod;

class PaymentMethodRepository
{
    public function getAllPaymentMethodLists($select=['*'],$with=[])
    {
        return PaymentMethod::with($with)->select($select)->get();
    }

    public function pluckAllPaymentMethodLists($select=['*'])
    {
        return PaymentMethod::where('status',1)
            ->select($select)
            ->get()->toArray();
    }

    public function findDetailById($id,$select=['*'])
    {
        return PaymentMethod::select($select)
            ->where('id',$id)
            ->first();
    }

    public function store($validatedData)
    {
        return PaymentMethod::insert($validatedData);
    }

    public function toggleStatus($paymentMethod)
    {
        return $paymentMethod->update([
            'status' => !$paymentMethod->status
        ]);
    }

    public function update($paymentMethod, $validatedData)
    {
        $paymentMethod->update($validatedData);
        return $paymentMethod->fresh();
    }


    public function delete($paymentMethod)
    {
        return $paymentMethod->delete();
    }
}
