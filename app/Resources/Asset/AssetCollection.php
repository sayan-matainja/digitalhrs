<?php

namespace App\Resources\Asset;



use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;


class AssetCollection extends ResourceCollection
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function toArray($request)
    {

        return AssetResources::collection($this->collection);
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array
     */
    public function with($request)
    {
        return [
            'status' => true,
            'code' => 200
        ];
    }

}





