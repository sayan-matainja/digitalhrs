<?php

namespace App\Resources\Comment;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CommentWithRepliesCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return CommentWithReplyResource::collection($this->collection);
    }

    public function with($request)
    {
        return [
            'status' => true,
            'code' => 200
        ];
    }

}
