<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\User;

it('returns stores for the authenticated user', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create(['user_id' => $user->id, 'name' => 'My Shop']);
    Product::factory()->count(3)->create(['store_id' => $store->id]);

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            query {
                myStores {
                    id
                    name
                    shopifyDomain
                    syncing
                    productsCount
                    connectedAt
                }
            }
        ',
    ]);

    $response->assertOk();
    $response->assertJsonCount(1, 'data.myStores');
    $response->assertJsonPath('data.myStores.0.name', 'My Shop');
    $response->assertJsonPath('data.myStores.0.productsCount', 3);
    $response->assertJsonPath('data.myStores.0.syncing', false);
});

it('returns empty array when user has no stores', function () {
    $user = User::factory()->create();

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            query {
                myStores {
                    id
                    name
                }
            }
        ',
    ]);

    $response->assertOk();
    $response->assertJsonCount(0, 'data.myStores');
});

it('does not return stores belonging to other users', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Store::factory()->create(['user_id' => $otherUser->id]);

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            query {
                myStores {
                    id
                }
            }
        ',
    ]);

    $response->assertOk();
    $response->assertJsonCount(0, 'data.myStores');
});

it('requires authentication', function () {
    $response = test()->postJson('/graphql', [
        'query' => '
            query {
                myStores {
                    id
                }
            }
        ',
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
});
