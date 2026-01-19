<?php


/**
 * Created by PhpStorm.
 * User: sandeep
 * Date: 8/1/2021
 * Time: 5:20 PM
 */

namespace App\Resources\User;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
//    protected $cardHtml = '';
//    public function cardHtml(string $html): self
//    {
//        $this->cardHtml = $html;
//        return $this;
//    }
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'phone' => $this->phone,
            'dob' => $this->dob,
            'gender' => ucfirst($this->gender),
            'address' => ucfirst($this->address),
            'status' => ucfirst($this->status),
            'leave_allocated' => $this->leave_allocated,
            'employment_type' => removeSpecialChars($this->employment_type),
            'office_time' => ($this->officeTime->opening_time). ' - ' .($this->officeTime->closing_time),
            'branch' => ucfirst($this->branch?->name),
            'department' => ucfirst($this->department?->dept_name),
            'post' => ucfirst($this->post?->post_name),
            'role' => ucfirst($this->role?->name),
            'avatar' => ($this->avatar) ? asset(User::AVATAR_UPLOAD_PATH.$this->avatar)  : asset('assets/images/img.png'),
            'joining_date' => !is_null($this->joining_date) ? ($this->joining_date) : 'N/A',
            'bank_name' => !is_null($this?->accountDetail?->bank_name) ? removeSpecialChars($this?->accountDetail?->bank_name):'N/A',
            'bank_account_no' =>  !is_null($this?->accountDetail?->bank_account_no) ? ($this?->accountDetail?->bank_account_no):'N/A',
            'bank_account_type' => !is_null($this?->accountDetail?->bank_account_type) ? removeSpecialChars($this?->accountDetail?->bank_account_type):'N/A',
//            'card_html' => $this->cardHtml,
            'card_link' =>route('employee.card.view', $this->employee_code),
        ];
    }
}












