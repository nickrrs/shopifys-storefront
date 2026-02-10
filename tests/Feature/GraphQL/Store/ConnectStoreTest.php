<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

it('connects a store successfully via shopify api', function () {
    Http::fake([
        '*' => Http::response([
            'data' => [
                'shop' => [
                    'name' => 'My Shopify Store',
                    'myshopifyDomain' => 'my-store.myshopify.com',
                ],
            ],
        ]),
    ]);

    $user = User::factory()->create();

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation ConnectStore($input: ConnectStoreInput!) {
                connectStore(input: $input) {
                    id
                    name
                    shopifyDomain
                }
            }
        ',
        'variables' => [
            'input' => [
                'name' => 'My Store',
                'shopifyDomain' => 'my-store.myshopify.com',
                'accessToken' => 'shpat_test123',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.connectStore.name', 'My Store');
    $response->assertJsonPath('data.connectStore.shopifyDomain', 'my-store.myshopify.com');

    test()->assertDatabaseHas('stores', [
        'user_id' => $user->id,
        'name' => 'My Store',
        'shopify_domain' => 'my-store.myshopify.com',
    ]);
});

it('fails when shopify api returns error', function () {
    Http::fake([
        '*' => Http::response('Unauthorized', 401),
    ]);

    $user = User::factory()->create();

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation ConnectStore($input: ConnectStoreInput!) {
                connectStore(input: $input) {
                    id
                }
            }
        ',
        'variables' => [
            'input' => [
                'name' => 'My Store',
                'shopifyDomain' => 'bad-store.myshopify.com',
                'accessToken' => 'invalid_token',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);

    test()->assertDatabaseMissing('stores', [
        'shopify_domain' => 'bad-store.myshopify.com',
    ]);
});

it('fails with empty input fields', function () {
    $user = User::factory()->create();

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation ConnectStore($input: ConnectStoreInput!) {
                connectStore(input: $input) {
                    id
                }
            }
        ',
        'variables' => [
            'input' => [
                'name' => '',
                'shopifyDomain' => '',
                'accessToken' => '',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
});

it('requires authentication', function () {
    $response = test()->postJson('/graphql', [
        'query' => '
            mutation ConnectStore($input: ConnectStoreInput!) {
                connectStore(input: $input) {
                    id
                }
            }
        ',
        'variables' => [
            'input' => [
                'name' => 'Test',
                'shopifyDomain' => 'test.myshopify.com',
                'accessToken' => 'shpat_test',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
});
