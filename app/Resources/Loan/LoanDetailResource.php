<?php

namespace App\Resources\Loan;

use App\Transformers\TadaAttachmentTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => ucfirst($this->title),
            'description' => ($this->description),
            'total_expense' => ($this->total_expense),
            'status' => ucfirst($this->status),
            'remark' => ($this->remark) ?? 'N/A',
            'employee' => ($this->whenLoaded('employeeDetail')) ? ucfirst($this->employeeDetail->name) : '',
            'verified_by' => ($this->whenLoaded('verifiedBy')) ? ucfirst($this->verifiedBy->name) : 'N/A',
            'submitted_date' => date('M d Y',strtotime($this->created_at)),
            'attachments' => (new TadaAttachmentTransformer($this->whenLoaded('attachments')))->transform()
        ];
    }
}
