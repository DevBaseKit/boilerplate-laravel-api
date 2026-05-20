<?php

namespace App\Repositories\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait AppliesQueryFilters
{
    /**
     * Apply common index filters (search, exact filters, order) to query.
     *
     * @param Builder $query
     * @param array $filters
     * @param array{
     *   search_fields?: array<int, string>,
     *   exact_filters?: array<string, string>,
     *   order_by?: string,
     *   dir?: string
     * } $config
     * @return Builder
     */
    protected function applyIndexFilters(Builder $query, array $filters = [], array $config = []): Builder
    {
        $search = $filters['search'] ?? null;
        $orderBy = $filters['order_by'] ?? ($config['order_by'] ?? 'created_at');
        $dir = $filters['dir'] ?? ($config['dir'] ?? 'desc');
        $searchFields = $config['search_fields'] ?? [];
        $exactFilters = $config['exact_filters'] ?? [];

        if (!empty($search) && count($searchFields) > 0) {
            $query->where(function (Builder $builder) use ($searchFields, $search): void {
                foreach ($searchFields as $index => $field) {
                    if ($index === 0) {
                        $builder->where($field, 'like', "%{$search}%");
                        continue;
                    }

                    $builder->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        foreach ($exactFilters as $filterKey => $column) {
            $value = $filters[$filterKey] ?? null;

            if ($value !== null && $value !== '') {
                $query->where($column, $value);
            }
        }

        return $query->orderBy($orderBy, $dir);
    }
}
