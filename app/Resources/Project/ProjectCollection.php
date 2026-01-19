<?php

namespace App\Resources\Project;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProjectCollection extends ResourceCollection
{

    public function toArray($request)
    {
        return ProjectResource::collection($this->collection);
    }

    public function with($request)
    {
        return [
            'status' => true,
            'code' => 200
        ];
    }

}





