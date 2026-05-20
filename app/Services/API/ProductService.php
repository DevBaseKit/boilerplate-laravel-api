<?php

namespace App\Services\API;

use App\Repositories\API\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService implements ProductServiceInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * ProductService constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get all products.
     */
    public function getAllProducts(array $filters = []): LengthAwarePaginator
    {
        return $this->productRepository->paginate($filters);
    }

    /**
     * Get product by ID.
     */
    public function getProductById(mixed $id): Model
    {
        return $this->productRepository->findOrFail($id);
    }

    /**
     * Create a new product.
     */
    public function createProduct(array $data): Model
    {
        return $this->productRepository->create($data);
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(mixed $id, array $data): Model
    {
        $product = $this->productRepository->findOrFail($id);
        return $this->productRepository->updateModel($product, $data);
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(mixed $id): bool
    {
        $product = $this->productRepository->findOrFail($id);
        return $this->productRepository->deleteModel($product);
    }
}
