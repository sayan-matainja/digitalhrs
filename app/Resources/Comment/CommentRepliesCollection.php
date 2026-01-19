<?php

namespace App\Resources\Comment;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CommentRepliesCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ReplyResource::collection($this->collection);
    }

    public function with($request)
    {
        return [
            'status' => true,
            'code' => 200
        ];
    }
}
