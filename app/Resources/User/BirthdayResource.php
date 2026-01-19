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

class BirthdayResource extends JsonResource
{


    public function toArray($request)
    {
        return [
            'id' => $this->id  ?? '',
            'name' => $this->name,
            'dob' => $this->dob ?? '',
            'post' => ucfirst($this->post?->post_name),
            'avatar' => ($this->avatar) ? asset(User::AVATAR_UPLOAD_PATH.$this->avatar)  : asset('assets/images/img.png'),
        ];
    }
}












