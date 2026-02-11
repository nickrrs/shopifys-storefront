<?php

use App\Models\Store;
use App\Services\Shopify\ShopifyGraphqlClient;
use Illuminate\Support\Facades\Http;

it('builds correct endpoint url', function () {
    $store = Store::factory()->create([
        'shopify_domain' => 'test-shop.myshopify.com',
    ]);

    $client = new ShopifyGraphqlClient($store, '2026-01');
    $reflection = new ReflectionMethod($client, 'endpoint');
    $endpoint = $reflection->invoke($client);

    expect($endpoint)->toBe('https://test-shop.myshopify.com/admin/api/2026-01/graphql.json');
});

it('strips trailing slash from domain', function () {
    $store = Store::factory()->create([
        'shopify_domain' => 'test-shop.myshopify.com/',
    ]);

    $client = new ShopifyGraphqlClient($store, '2026-01');
    $reflection = new ReflectionMethod($client, 'endpoint');
    $endpoint = $reflection->invoke($client);

    expect($endpoint)->toBe('https://test-shop.myshopify.com/admin/api/2026-01/graphql.json');
});

it('sends correct headers and returns data on success', function () {
    $store = Store::factory()->create([
        'shopify_domain' => 'test-shop.myshopify.com',
        'access_token' => 'shpat_test123',
    ]);

    Http::fake([
        'https://test-shop.myshopify.com/*' => Http::response([
            'data' => ['shop' => ['name' => 'Test Shop']],
        ]),
    ]);

    $client = new ShopifyGraphqlClient($store, '2026-01');
    $result = $client->request('query { shop { name } }');

    expect($result)->toBe(['shop' => ['name' => 'Test Shop']]);

    Http::assertSent(function ($request) {
        return $request->hasHeader('X-Shopify-Access-Token', 'shpat_test123')
            && $request->hasHeader('Content-Type', 'application/json');
    });
});

it('casts variables as object in payload', function () {
    $store = Store::factory()->create([
        'shopify_domain' => 'test-shop.myshopify.com',
    ]);

    Http::fake([
        '*' => Http::response(['data' => ['products' => []]]),
    ]);

    $client = new ShopifyGraphqlClient($store, '2026-01');
    $client->request('query ($first: Int!) { products(first: $first) { edges { node { id } } } }', ['first' => 10]);

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return isset($body['variables']) && $body['variables']['first'] === 10;
    });
});

it('throws RuntimeException on HTTP error', function () {
    $store = Store::factory()->create([
        'shopify_domain' => 'test-shop.myshopify.com',
    ]);

    Http::fake([
        '*' => Http::response('Unauthorized', 401),
    ]);

    $client = new ShopifyGraphqlClient($store, '2026-01');

    expect(fn () => $client->request('query { shop { name } }'))
        ->toThrow(RuntimeException::class);
});

it('throws RuntimeException on GraphQL errors', function () {
    $store = Store::factory()->create([
        'shopify_domain' => 'test-shop.myshopify.com',
    ]);

    Http::fake([
        '*' => Http::response([
            'data' => null,
            'errors' => [['message' => 'Access denied']],
        ]),
    ]);

    $client = new ShopifyGraphqlClient($store, '2026-01');

    expect(fn () => $client->request('query { shop { name } }'))
        ->toThrow(RuntimeException::class);
});
