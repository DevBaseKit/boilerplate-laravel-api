<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginationFormatter
{
    /**
     * Build standardized pagination payload.
     *
     * @param LengthAwarePaginator $paginator
     * @param array $data
     * @return array{
     *   total:int,
     *   per_page:int,
     *   current_page:int,
     *   last_page:int,
     *   first_page:int,
     *   data:array
     * }
     */
    public static function format(LengthAwarePaginator $paginator, array $data): array
    {
        return [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'first_page' => 1,
            'data' => $data,
        ];
    }
}
