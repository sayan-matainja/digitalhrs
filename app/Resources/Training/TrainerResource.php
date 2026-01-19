<?php

namespace App\Resources\Training;

use App\Enum\TrainerTypeEnum;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerResource extends JsonResource
{
    public function toArray($request)
    {
        if($this->trainer_type == TrainerTypeEnum::internal->value){
            $name = $this?->employee?->name;
            $phone = $this?->employee?->phone;
            $email = $this?->employee?->email;
            $address = $this?->employee?->address;
            $expertise = $this?->employee?->department?->dept_name;
        }else{
            $name = $this->name;
            $phone = $this->phone;
            $email = $this->email;
            $address = $this->address;
            $expertise = $this->expertise;
        }
        return [
            'id' => $this->id ?? '',
            'trainer_type' => ucfirst($this->trainer_type),
            'name' => ucfirst($name) ?? '',
            'email' => ucfirst($email) ?? '',
            'phone' => ucfirst($phone) ?? '',
            'address' => ucfirst($address) ?? '',
            'expertise' => ucfirst($expertise) ?? '',
            'is_trainer' => $this->employee_id == auth()->user()->id,
            'user_id' => $this->employee_id ?? 0,
        ];
    }
}
