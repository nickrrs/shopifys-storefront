<?php

use App\Models\Store;
use App\Models\User;
use App\Services\Shopify\ShopifyStoreConnector;
use Illuminate\Support\Facades\Http;

it('creates a new store when connecting successfully', function () {
    Http::fake([
        '*' => Http::response([
            'data' => [
                'shop' => [
                    'name' => 'Test Shop',
                    'myshopifyDomain' => 'test-shop.myshopify.com',
                ],
            ],
        ]),
    ]);

    $user = User::factory()->create();
    $connector = new ShopifyStoreConnector;

    $store = $connector->connect($user, 'My Store', 'test-shop.myshopify.com', 'shpat_abc123');

    expect($store)->toBeInstanceOf(Store::class);
    expect($store->name)->toBe('My Store');
    expect($store->shopify_domain)->toBe('test-shop.myshopify.com');
    expect($store->user_id)->toBe($user->id);
    expect($store->connected_at)->not->toBeNull();

    test()->assertDatabaseHas('stores', [
        'user_id' => $user->id,
        'shopify_domain' => 'test-shop.myshopify.com',
    ]);
});

it('uses resolved domain from shopify response', function () {
    Http::fake([
        '*' => Http::response([
            'data' => [
                'shop' => [
                    'name' => 'Test Shop',
                    'myshopifyDomain' => 'resolved-domain.myshopify.com',
                ],
            ],
        ]),
    ]);

    $user = User::factory()->create();
    $connector = new ShopifyStoreConnector;

    $store = $connector->connect($user, 'My Store', 'custom-domain.com', 'shpat_abc123');

    expect($store->shopify_domain)->toBe('resolved-domain.myshopify.com');
});

it('updates existing store when domain already exists', function () {
    Http::fake([
        '*' => Http::response([
            'data' => [
                'shop' => [
                    'name' => 'Test Shop',
                    'myshopifyDomain' => 'existing.myshopify.com',
                ],
            ],
        ]),
    ]);

    $user = User::factory()->create();

    $existingStore = Store::factory()->create([
        'user_id' => $user->id,
        'shopify_domain' => 'existing.myshopify.com',
        'name' => 'Old Name',
    ]);

    $connector = new ShopifyStoreConnector;
    $store = $connector->connect($user, 'New Name', 'existing.myshopify.com', 'shpat_new_token');

    expect($store->id)->toBe($existingStore->id);
    expect($store->name)->toBe('New Name');

    test()->assertDatabaseCount('stores', 1);
});

it('throws exception when shopify api fails', function () {
    Http::fake([
        '*' => Http::response('Unauthorized', 401),
    ]);

    $user = User::factory()->create();
    $connector = new ShopifyStoreConnector;

    expect(fn () => $connector->connect($user, 'My Store', 'bad-domain.com', 'invalid_token'))
        ->toThrow(RuntimeException::class);
});
