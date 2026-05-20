<?php

namespace App\Repositories\API;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * Get paginated products.
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator;

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
