<?php

namespace App\Resources\award;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AwardCollection extends ResourceCollection
{

    public function toArray($request)
    {
        return AwardResource::collection($this->collection);
    }

    public function with($request)
    {
        return [
            'status' => true,
            'code' => 200
        ];
    }

}





