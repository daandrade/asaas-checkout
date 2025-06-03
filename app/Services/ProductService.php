<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function getAllProducts()
    {
        return $this->productRepository->all();
    }

    public function getProductById(int $id): Product
    {
        $product = $this->productRepository->find($id);
        
        if (!$product) {
            abort(404, 'Product not found');
        }
        
        return $product;
    }

    public function createProduct(array $data): Product
    {
        $data['user_id'] = 1;
        return $this->productRepository->create($data);
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $this->checkOwnership($product);
        return $this->productRepository->update($product, $data);
    }

    public function deleteProduct(Product $product): void
    {
        $this->checkOwnership($product);
        $this->productRepository->delete($product);
    }

    public function getUserProducts(int $userId)
    {
        return $this->productRepository->getByUser($userId);
    }

    private function checkOwnership(Product $product): void
    {
        if (1 !== $product->user_id) {
            throw new AuthorizationException('Unauthorized action.');
        }
    }
}