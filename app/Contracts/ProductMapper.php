<?php

namespace App\Contracts;

use App\Models\Product;

interface ProductMapper
{
    public function toShopifyInput(Product $product): array;
    public function fromShopifyNode(array $payload): Product;
}

