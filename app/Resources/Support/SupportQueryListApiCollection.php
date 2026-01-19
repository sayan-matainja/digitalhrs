<?php

namespace App\Resources\Support;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SupportQueryListApiCollection extends ResourceCollection
{

    public function toArray($request)
    {
        return [
            'meta' => [
                'pagination' => [
                    'total' => $this->total(),
                    'count' => $this->count(),
                    'per_page' => $this->perPage(),
                    'current_page' => $this->currentPage(),
                    'total_pages' => $this->lastPage(),
                ],
            ],
            'data' => SupportListApiResource::collection($this->collection),
        ];
    }

    public function with($request)
    {
        return [
            'status' => true,
            'status_code' => 200,

        ];
    }


}





