<?php

namespace App\Resources\Loan;

use App\Resources\Tada\TadaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LoanTypeCollection extends ResourceCollection
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|AnonymousResourceCollection
     */
    public function toArray($request)
    {
        return LoanTypeResource::collection($this->collection);
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
