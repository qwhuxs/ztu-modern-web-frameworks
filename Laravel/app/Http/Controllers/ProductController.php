<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    private const PRODUCTS = [
        ['id' => 1, 'name' => 'iPhone 15', 'description' => 'Smartphone Apple', 'price' => 39999],
        ['id' => 2, 'name' => 'Samsung Galaxy S24', 'description' => 'Smartphone Samsung', 'price' => 34999],
        ['id' => 3, 'name' => 'MacBook Air M3', 'description' => 'Laptop Apple', 'price' => 59999],
    ];

    /**
     * @return mixed
     */
    public function getProducts(): mixed
    {
        return response()->json(self::PRODUCTS, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function getProductItem(string $id): mixed
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return response()->json(['data' => ['error' => 'Not found product by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $product], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function createProduct(Request $request): mixed
    {
        $requestData = json_decode($request->getContent(), true);

        $productId = random_int(1, 100);

        $newProductData = [
            'id' => $productId,
            'name' => $requestData['name'],
            'description' => $requestData['description'],
            'price' => $requestData['price']
        ];

        // TODO insert to db

        return response()->json(['data' => $newProductData], Response::HTTP_CREATED);
    }

    /**
     * @param string $id
     * @param Request $request
     * @return mixed
     */
    public function updateProduct(string $id, Request $request): mixed
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return response()->json(['data' => ['error' => 'Not found product by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true);

        $updatedProductData = [
            'id' => $product['id'],
            'name' => $requestData['name'] ?? $product['name'],
            'description' => $requestData['description'] ?? $product['description'],
            'price' => $requestData['price'] ?? $product['price']
        ];

        // TODO update in db

        return response()->json(['data' => $updatedProductData], Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function deleteProduct(string $id): mixed
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return response()->json(['data' => ['error' => 'Not found product by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        // TODO delete from db

        return response()->json(['data' => ['id' => $product['id']], 'message' => 'Product deleted'], Response::HTTP_OK);
    }

    /**
     * @param array $products
     * @param string $id
     * @return array|null
     */
    private function getProductItemById(array $products, string $id): ?array
    {
        foreach ($products as $product) {
            if ($product['id'] == $id) {
                return $product;
            }
        }

        return null;
    }
}