<?php

namespace App\Resources\Project;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProjectTaskCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ProjectTaskResource::collection($this->collection);
    }

    public function with($request)
    {
        return [
            'status' => true,
            'code' => 200,
        ];
    }
}
