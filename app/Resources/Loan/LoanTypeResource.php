<?php

namespace App\Resources\Loan;

use App\Transformers\TadaAttachmentTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanTypeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => ucfirst($this->name),
            'minimum_amount' => $this->minimum_amount,
            'maximum_amount' => $this->maximum_amount,
            'interest_rate' => $this->interest_rate,
            'interest_type' => $this->interest_type,
            'term' =>$this->term,
        ];
    }
}
