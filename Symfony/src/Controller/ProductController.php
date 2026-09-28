<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    private const PRODUCTS = [
        ['id' => 1, 'name' => 'iPhone 15', 'description' => 'Smartphone Apple', 'price' => 39999],
        ['id' => 2, 'name' => 'Samsung Galaxy S24', 'description' => 'Smartphone Samsung', 'price' => 34999],
        ['id' => 3, 'name' => 'MacBook Air M3', 'description' => 'Laptop Apple', 'price' => 59999],
    ];

    /**
     * @return JsonResponse
     */
    #[Route('/products', name: 'get_products', methods: [Request::METHOD_GET])]
    public function getProducts(): JsonResponse
    {
        return new JsonResponse(['data' => self::PRODUCTS], Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    #[Route('/products/{id}', name: 'get_product_item', methods: [Request::METHOD_GET])]
    public function getProductItem(string $id): JsonResponse
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return new JsonResponse(['data' => ['error' => 'Not found product by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $product], Response::HTTP_OK);
    }

    #[Route('/products', name: 'post_products', methods: [Request::METHOD_POST])]
    public function createProduct(Request $request): JsonResponse
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

        return new JsonResponse(['data' => $newProductData], Response::HTTP_CREATED);
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