<?php

namespace App\Resources\Notification;


use Illuminate\Http\Resources\Json\ResourceCollection;


class NotificationCollection extends ResourceCollection
{

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => NotificationResource::collection($this->collection),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function with($request)
    {
        return [
            'status' => true,
            'status_code' => 200
        ];
    }


}





