<?php

use App\Jobs\SyncProductsJob;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\Http;

it('syncs products from shopify and persists them locally', function () {
    $store = Store::factory()->create(['syncing' => true]);

    Http::fake([
        '*' => Http::response([
            'data' => [
                'products' => [
                    'edges' => [
                        [
                            'cursor' => 'cursor1',
                            'node' => [
                                'id' => 'gid://shopify/Product/1',
                                'title' => 'Product One',
                                'descriptionHtml' => '<p>Description</p>',
                                'status' => 'ACTIVE',
                                'variants' => [
                                    'edges' => [
                                        [
                                            'node' => [
                                                'price' => '29.99',
                                                'inventoryQuantity' => 100,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cursor' => 'cursor2',
                            'node' => [
                                'id' => 'gid://shopify/Product/2',
                                'title' => 'Product Two',
                                'descriptionHtml' => null,
                                'status' => 'DRAFT',
                                'variants' => [
                                    'edges' => [],
                                ],
                            ],
                        ],
                    ],
                    'pageInfo' => ['hasNextPage' => false],
                ],
            ],
        ]),
    ]);

    (new SyncProductsJob($store->id))->handle();

    expect(Product::where('store_id', $store->id)->count())->toBe(2);

    $product1 = Product::where('shopify_product_id', 'gid://shopify/Product/1')->first();
    expect($product1->title)->toBe('Product One');
    expect($product1->price)->toBe('29.99');
    expect($product1->inventory_quantity)->toBe(100);

    $product2 = Product::where('shopify_product_id', 'gid://shopify/Product/2')->first();
    expect($product2->title)->toBe('Product Two');
    expect($product2->price)->toBe('0.00');
    expect($product2->inventory_quantity)->toBeNull();

    expect($store->fresh()->syncing)->toBeFalse();
});

it('sets syncing to false even when an error occurs', function () {
    $store = Store::factory()->create(['syncing' => false]);

    Http::fake([
        '*' => Http::response('Server Error', 500),
    ]);

    (new SyncProductsJob($store->id))->handle();

    expect($store->fresh()->syncing)->toBeFalse();
});

it('does nothing when store does not exist', function () {
    Http::fake();

    (new SyncProductsJob(99999))->handle();

    Http::assertNothingSent();
});

it('handles pagination with multiple pages', function () {
    $store = Store::factory()->create(['syncing' => true]);

    Http::fake([
        '*' => Http::sequence()
            ->push([
                'data' => [
                    'products' => [
                        'edges' => [
                            [
                                'cursor' => 'page1_cursor',
                                'node' => [
                                    'id' => 'gid://shopify/Product/1',
                                    'title' => 'Page 1 Product',
                                    'descriptionHtml' => null,
                                    'status' => 'ACTIVE',
                                    'variants' => ['edges' => [['node' => ['price' => '10.00', 'inventoryQuantity' => 5]]]],
                                ],
                            ],
                        ],
                        'pageInfo' => ['hasNextPage' => true],
                    ],
                ],
            ])
            ->push([
                'data' => [
                    'products' => [
                        'edges' => [
                            [
                                'cursor' => 'page2_cursor',
                                'node' => [
                                    'id' => 'gid://shopify/Product/2',
                                    'title' => 'Page 2 Product',
                                    'descriptionHtml' => null,
                                    'status' => 'DRAFT',
                                    'variants' => ['edges' => [['node' => ['price' => '20.00', 'inventoryQuantity' => 10]]]],
                                ],
                            ],
                        ],
                        'pageInfo' => ['hasNextPage' => false],
                    ],
                ],
            ]),
    ]);

    (new SyncProductsJob($store->id))->handle();

    expect(Product::where('store_id', $store->id)->count())->toBe(2);
    expect($store->fresh()->syncing)->toBeFalse();
});

it('updates existing products instead of duplicating', function () {
    $store = Store::factory()->create(['syncing' => true]);

    Product::factory()->create([
        'store_id' => $store->id,
        'shopify_product_id' => 'gid://shopify/Product/1',
        'title' => 'Old Title',
        'price' => 5.00,
    ]);

    Http::fake([
        '*' => Http::response([
            'data' => [
                'products' => [
                    'edges' => [
                        [
                            'cursor' => 'c1',
                            'node' => [
                                'id' => 'gid://shopify/Product/1',
                                'title' => 'New Title',
                                'descriptionHtml' => null,
                                'status' => 'ACTIVE',
                                'variants' => ['edges' => [['node' => ['price' => '15.00', 'inventoryQuantity' => 50]]]],
                            ],
                        ],
                    ],
                    'pageInfo' => ['hasNextPage' => false],
                ],
            ],
        ]),
    ]);

    (new SyncProductsJob($store->id))->handle();

    $products = Product::where('store_id', $store->id)->get();
    expect($products)->toHaveCount(1);
    expect($products->first()->title)->toBe('New Title');
    expect($products->first()->price)->toBe('15.00');
});
