<?php

namespace App\Resources\Task;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskCollection extends ResourceCollection
{

    public function toArray($request)
    {
        return [
            'data' => TaskListingResource::collection($this->collection),
        ];
    }

    public function with($request)
    {
        return [
            'status' => true,
            'code' => 200,
        ];
    }

}






