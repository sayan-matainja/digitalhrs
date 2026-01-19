<?php

namespace App\Resources\AssignedMember;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AssignedMemberCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return AssignedMemberResource::collection($this->collection);
    }

    public function with($request)
    {
        return [
            'status' => true,
            'code' => 200
        ];
    }

}






