<?php

namespace App\Http\Responses;

use Illuminate\Http\Resources\Json\PaginatedResourceResponse;

class CustomPaginatedResourceResponse extends PaginatedResourceResponse
{

    protected function paginationInformation($request)
    {
        $paginated = $this->resource->resource->toArray();

        $default = [
            'links' => $this->paginationLinks($paginated),
        ];

        if (method_exists($this->resource, 'paginationInformation')) {
            return $this->resource->paginationInformation($request, $paginated, $default);
        }

        return $default;
    }
}
