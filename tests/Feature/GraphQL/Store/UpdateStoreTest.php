<?php

use App\Models\Store;
use App\Models\User;

it('updates store name', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation UpdateStore($id: ID!, $input: UpdateStoreInput!) {
                updateStore(id: $id, input: $input) {
                    id
                    name
                }
            }
        ',
        'variables' => [
            'id' => (string) $store->id,
            'input' => [
                'name' => 'New Name',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.updateStore.name', 'New Name');

    expect($store->fresh()->name)->toBe('New Name');
});

it('fails to update store belonging to another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $store = Store::factory()->create(['user_id' => $otherUser->id]);

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation UpdateStore($id: ID!, $input: UpdateStoreInput!) {
                updateStore(id: $id, input: $input) {
                    id
                    name
                }
            }
        ',
        'variables' => [
            'id' => (string) $store->id,
            'input' => [
                'name' => 'Hacked Name',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);

    expect($store->fresh()->name)->not->toBe('Hacked Name');
});

it('fails to update non-existent store', function () {
    $user = User::factory()->create();

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation UpdateStore($id: ID!, $input: UpdateStoreInput!) {
                updateStore(id: $id, input: $input) {
                    id
                }
            }
        ',
        'variables' => [
            'id' => '99999',
            'input' => [
                'name' => 'Ghost Store',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
});

it('requires authentication', function () {
    $store = Store::factory()->create();

    $response = test()->postJson('/graphql', [
        'query' => '
            mutation UpdateStore($id: ID!, $input: UpdateStoreInput!) {
                updateStore(id: $id, input: $input) { id }
            }
        ',
        'variables' => [
            'id' => (string) $store->id,
            'input' => ['name' => 'Test'],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
});
