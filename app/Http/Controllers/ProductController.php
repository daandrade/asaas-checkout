<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function index()
    {
        $products = $this->productService->getAllProducts();
        return response()->json([
            'status' => 'success',
            'data' => ProductResource::collection($products)
        ]);
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct($request->validated());
        return response()->json([
            'status' => 'success',
            'data' => new ProductResource($product)
        ], Response::HTTP_CREATED);
    }

    public function show(int $id)
    {
        $product = $this->productService->getProductById($id);
        return response()->json([
            'status' => 'success',
            'data' => new ProductResource($product)
        ]);
    }

    public function update(ProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);
        $updatedProduct = $this->productService->updateProduct($product, $request->validated());
        return response()->json([
            'status' => 'success',
            'data' => new ProductResource($updatedProduct)
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);
        $this->productService->deleteProduct($product);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function myProducts()
    {
        $products = $this->productService->getUserProducts(auth()->id());
        return response()->json([
            'status' => 'success',
            'data' => ProductResource::collection($products)
        ]);
    }
}