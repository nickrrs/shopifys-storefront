<?php

namespace App\Services\Shopify;

use App\Contracts\IntegrationClient;
use App\Models\Store;

class ShopifyGraphqlClientFactory
{
    public function create(Store $store): IntegrationClient
    {
        return new ShopifyGraphqlClient($store);
    }

    public function createFromCredentials(string $shopifyDomain, string $accessToken): IntegrationClient
    {
        $tempStore = new Store([
            'shopify_domain' => $shopifyDomain,
            'access_token' => $accessToken,
        ]);

        return new ShopifyGraphqlClient($tempStore);
    }
}
