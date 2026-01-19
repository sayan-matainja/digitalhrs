<?php

namespace App\Resources\User;

use App\Resources\award\AwardResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BirthdayCollection extends ResourceCollection
{

    public function toArray($request)
    {
        return BirthdayResource::collection($this->collection);
    }

    public function with($request)
    {
        return [
            'status' => true,
            'code' => 200
        ];
    }

}





