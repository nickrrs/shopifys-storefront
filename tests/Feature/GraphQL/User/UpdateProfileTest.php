<?php

use App\Models\User;

it('updates user profile name and email', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation UpdateProfile($input: UpdateProfileInput!) {
                updateProfile(input: $input) {
                    id
                    name
                    email
                }
            }
        ',
        'variables' => [
            'input' => [
                'name' => 'New Name',
                'email' => 'new@example.com',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.updateProfile.name', 'New Name');
    $response->assertJsonPath('data.updateProfile.email', 'new@example.com');

    $user->refresh();
    expect($user->name)->toBe('New Name');
    expect($user->email)->toBe('new@example.com');
});

it('fails with duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['email' => 'mine@example.com']);

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation UpdateProfile($input: UpdateProfileInput!) {
                updateProfile(input: $input) { id }
            }
        ',
        'variables' => [
            'input' => [
                'name' => 'Test',
                'email' => 'taken@example.com',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
});

it('requires authentication', function () {
    $response = test()->postJson('/graphql', [
        'query' => '
            mutation UpdateProfile($input: UpdateProfileInput!) {
                updateProfile(input: $input) { id }
            }
        ',
        'variables' => [
            'input' => [
                'name' => 'Test',
                'email' => 'test@example.com',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
});
