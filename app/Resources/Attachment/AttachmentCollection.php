<?php

namespace App\Resources\Attachment;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AttachmentCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return AttachmentResource::collection($this->collection);
    }

    public function with($request)
    {
        return [
            'status' => true,
            'code' => 200
        ];
    }

}
