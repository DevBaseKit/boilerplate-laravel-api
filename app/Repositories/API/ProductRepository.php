<?php

namespace App\Repositories\API;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Get paginated products.
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Product::paginate($perPage);
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
