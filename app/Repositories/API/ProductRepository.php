<?php

namespace App\Repositories\API;

use App\Contracts\Repositories\API\ProductRepositoryInterface;
use App\Models\Product;
use App\Repositories\Concerns\AppliesQueryFilters;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    use AppliesQueryFilters;

    /**
     * Get paginated products.
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $page = (int) ($filters['page'] ?? 1);
        $limit = (int) ($filters['limit'] ?? 10);

        $query = Product::query();

        $query = $this->applyIndexFilters($query, $filters, [
            'search_fields' => ['name', 'description'],
            'exact_filters' => [],
            'order_by' => 'created_at',
            'dir' => 'desc',
        ]);

        return $query
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Find product by id or fail.
     */
    public function findOrFail(mixed $id): Product
    {
        return Product::findOrFail($id);
    }

    /**
     * Create product.
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update product model instance.
     */
    public function updateModel(Product $product, array $data): Product
    {
        $product->update($data);
        return $product;
    }

    /**
     * Delete product model instance.
     */
    public function deleteModel(Product $product): bool
    {
        return $product->delete();
    }
}
