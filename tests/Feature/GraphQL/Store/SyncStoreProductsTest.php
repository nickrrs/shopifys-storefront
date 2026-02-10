<?php

use App\Jobs\SyncProductsJob;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

it('dispatches sync job and sets syncing to true', function () {
    Queue::fake();

    $user = User::factory()->create();
    $store = Store::factory()->create(['user_id' => $user->id, 'syncing' => false]);

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation SyncStoreProducts($storeId: ID!) {
                syncStoreProducts(storeId: $storeId) {
                    id
                    syncing
                }
            }
        ',
        'variables' => [
            'storeId' => (string) $store->id,
        ],
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.syncStoreProducts.syncing', true);

    Queue::assertPushed(SyncProductsJob::class);
});

it('prevents sync when store is already syncing', function () {
    Queue::fake();

    $user = User::factory()->create();
    $store = Store::factory()->create(['user_id' => $user->id, 'syncing' => true]);

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation SyncStoreProducts($storeId: ID!) {
                syncStoreProducts(storeId: $storeId) {
                    id
                }
            }
        ',
        'variables' => [
            'storeId' => (string) $store->id,
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);

    Queue::assertNothingPushed();
});

it('fails to sync store belonging to another user', function () {
    Queue::fake();

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $store = Store::factory()->create(['user_id' => $otherUser->id]);

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation SyncStoreProducts($storeId: ID!) {
                syncStoreProducts(storeId: $storeId) {
                    id
                }
            }
        ',
        'variables' => [
            'storeId' => (string) $store->id,
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);

    Queue::assertNothingPushed();
});

it('fails to sync non-existent store', function () {
    Queue::fake();

    $user = User::factory()->create();

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation SyncStoreProducts($storeId: ID!) {
                syncStoreProducts(storeId: $storeId) {
                    id
                }
            }
        ',
        'variables' => [
            'storeId' => '99999',
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);

    Queue::assertNothingPushed();
});

it('requires authentication', function () {
    $store = Store::factory()->create();

    $response = test()->postJson('/graphql', [
        'query' => '
            mutation SyncStoreProducts($storeId: ID!) {
                syncStoreProducts(storeId: $storeId) { id }
            }
        ',
        'variables' => [
            'storeId' => (string) $store->id,
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
});
