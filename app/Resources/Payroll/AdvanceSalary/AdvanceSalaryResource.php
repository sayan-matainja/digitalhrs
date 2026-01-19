<?php

namespace App\Resources\Payroll\AdvanceSalary;

use App\Helpers\AppHelper;
use App\Transformers\AdvanceSalaryDocumentTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class AdvanceSalaryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'requested_amount' => ($this->requested_amount) ?? 0,
            'released_amount' => ($this->released_amount) ?? 0,
            'requested_date' => AppHelper::formatDateForView($this->advance_requested_date),
            'released_date' => $this->amount_granted_date ? AppHelper::formatDateForView($this->amount_granted_date) : 'N/A',
            'description' => ($this->description),
            'status' => ($this->status),
            'is_settled' => (bool)$this->is_settled,
            'remark' => ($this->remark) ?? 'N/A',
            'verified_by' => $this->verifiedBy ? $this->verifiedBy?->name : 'N/A',
            'documents' => $this->whenLoaded('attachments') ? (new AdvanceSalaryDocumentTransformer($this->whenLoaded('attachments')))->transform() : [],

        ];
    }


}
