<?php

namespace App\Services\Shopify;

use App\Contracts\IntegrationClient;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ShopifyStoreConnector
{
    public function connect(User $user, string $name, string $shopifyDomain, string $accessToken, IntegrationClient $client): Store
    {
        Log::info('ShopifyStoreConnector: connecting store.', [
            'user_id' => $user->id,
            'shopify_domain' => $shopifyDomain,
        ]);

        $store = new Store([
            'shopify_domain' => $shopifyDomain,
            'access_token' => $accessToken,
        ]);

        $response = $client->request(
            <<<'GRAPHQL'
            query GetShop {
              shop {
                name
                myshopifyDomain
              }
            }
            GRAPHQL
        );

        $shop = Arr::get($response, 'shop');

        $resolvedDomain = $shop['myshopifyDomain'] ?? $shopifyDomain;

        /** @var Store $store */
        $store = Store::updateOrCreate(
            ['shopify_domain' => $resolvedDomain],
            [
                'user_id' => $user->id,
                'name' => $name,
                'access_token' => $accessToken,
                'connected_at' => now(),
            ],
        );

        Log::info('ShopifyStoreConnector: store connected.', [
            'store_id' => $store->id,
            'resolved_domain' => $resolvedDomain,
        ]);

        return $store;
    }
}
