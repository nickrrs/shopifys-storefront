<?php

use App\Models\User;

it('logs out an authenticated user', function () {
    $user = User::factory()->create();

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation {
                logout
            }
        ',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.logout', true);
    test()->assertGuest();
});

it('fails to logout when unauthenticated', function () {
    $response = test()->postJson('/graphql', [
        'query' => '
            mutation {
                logout
            }
        ',
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
});
