<?php

namespace App\Services\API;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductServiceInterface
{
    /**
     * Get all products.
     *
     * @return LengthAwarePaginator
     */
    public function getAllProducts(int $perPage = 10): LengthAwarePaginator;

    /**
     * Get product by ID.
     *
     * @param mixed $id
     * @return Model
     */
    public function getProductById(mixed $id): Model;

    /**
     * Create a new product.
     *
     * @param array $data
     * @return Model
     */
    public function createProduct(array $data): Model;

    /**
     * Update an existing product.
     *
     * @param mixed $id
     * @param array $data
     * @return Model
     */
    public function updateProduct(mixed $id, array $data): Model;

    /**
     * Delete a product.
     *
     * @param mixed $id
     * @return bool
     */
    public function deleteProduct(mixed $id): bool;
}
