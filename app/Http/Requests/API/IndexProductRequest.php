<?php

namespace App\Http\Requests\API;

class IndexProductRequest extends BaseIndexRequest
{
    /**
     * Allowed sortable fields for products index.
     *
     * @return array<int, string>
     */
    protected function allowedSortFields(): array
    {
        return [
            'name',
            'price',
            'stock',
            'created_at',
            'updated_at',
        ];
    }
}
