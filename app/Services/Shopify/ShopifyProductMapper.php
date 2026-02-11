<?php

namespace App\Services\Shopify;

use App\Contracts\ProductMapper;
use App\Models\Product;

class ShopifyProductMapper implements ProductMapper
{
    public function toShopifyInput(Product $product): array
    {
        return [
            'title' => $product->title,
            'descriptionHtml' => $product->description,
            'status' => $product->status === 'active' ? 'ACTIVE' : 'DRAFT',
            'variants' => [
                [
                    'price' => (string) $product->price,
                    'inventoryQuantity' => $product->inventory_quantity ?? 0,
                ],
            ],
        ];
    }

    public function fromShopifyNode(array $productNode): Product
    {
        $edges = $productNode['variants']['edges'] ?? [];
        $variantNode = $edges[0]['node'] ?? null;

        return new Product([
            'title' => $productNode['title'] ?? '',
            'description' => $productNode['descriptionHtml'] ?? $productNode['description'] ?? null,
            'price' => isset($variantNode['price']) ? (float) $variantNode['price'] : 0.0,
            'inventory_quantity' => $variantNode['inventoryQuantity'] ?? $productNode['totalInventory'] ?? null,
            'status' => ($productNode['status'] ?? 'DRAFT') === 'ACTIVE' ? 'active' : 'draft',
        ]);
    }
}
