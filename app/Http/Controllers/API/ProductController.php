<?php

namespace App\Http\Controllers\API;

use App\Constants\ApiMessage;
use App\Constants\ApiStatusCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\IndexProductRequest;
use App\Http\Requests\API\StoreProductRequest;
use App\Http\Requests\API\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\API\ProductServiceInterface;
use App\Services\AuditTrailService;
use App\Support\PaginationFormatter;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use ApiResponseTrait;

    /**
     * @var ProductServiceInterface
     */
    protected ProductServiceInterface $productService;
    protected AuditTrailService $auditTrailService;

    /**
     * ProductController constructor.
     *
     * @param ProductServiceInterface $productService
     * @param AuditTrailService $auditTrailService
     */
    public function __construct(ProductServiceInterface $productService, AuditTrailService $auditTrailService)
    {
        $this->productService = $productService;
        $this->auditTrailService = $auditTrailService;
        $this->authorizeResource(Product::class, 'product');
    }

    /**
     * Display a listing of the products.
     *
     * @return JsonResponse
     */
    public function index(IndexProductRequest $request): JsonResponse
    {
        $products = $this->productService->getAllProducts($request->validated());
        $data = ProductResource::collection($products)->resolve();
        $payload = PaginationFormatter::format($products, $data);

        return $this->sendSuccess($payload, ApiMessage::SUCCESS);
    }

    /**
     * Store a newly created product in storage.
     *
     * @param StoreProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct(array_merge(
            $request->validated(),
            ['user_id' => $request->user('api')?->id]
        ));
        $this->auditTrailService->record('product.create', $product);
        return $this->sendSuccess(new ProductResource($product), ApiMessage::CREATED, ApiStatusCode::CREATED);
    }

    /**
     * Display the specified product.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        return $this->sendSuccess(new ProductResource($product), ApiMessage::SUCCESS);
    }

    /**
     * Update the specified product in storage.
     *
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $updatedProduct = $this->productService->updateProduct($product->id, $request->validated());
        $this->auditTrailService->record('product.update', $updatedProduct);
        return $this->sendSuccess(new ProductResource($updatedProduct), ApiMessage::UPDATED);
    }

    /**
     * Remove the specified product from storage.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->productService->deleteProduct($product->id);
        $this->auditTrailService->record('product.delete', $product);
        return $this->sendSuccess(null, ApiMessage::DELETED);
    }
}
