<?php

namespace App\Contracts\Repositories\API;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * Get paginated products.
     */
    public function paginate(array $filters = []): LengthAwarePaginator;

    /**
     * Find product by id or fail.
     */
    public function findOrFail(mixed $id): Product;

    /**
     * Create product.
     */
    public function create(array $data): Product;

    /**
     * Update product model instance.
     */
    public function updateModel(Product $product, array $data): Product;

    /**
     * Delete product model instance.
     */
    public function deleteModel(Product $product): bool;
}
