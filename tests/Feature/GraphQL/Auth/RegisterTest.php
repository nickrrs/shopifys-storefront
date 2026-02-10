<?php

use App\Models\User;

it('registers a new user and returns auth payload', function () {
    $response = test()->postJson('/graphql', [
        'query' => '
            mutation Register($input: RegisterInput!) {
                register(input: $input) {
                    user {
                        id
                        name
                        email
                    }
                }
            }
        ',
        'variables' => [
            'input' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.register.user.name', 'John Doe');
    $response->assertJsonPath('data.register.user.email', 'john@example.com');

    test()->assertDatabaseHas('users', ['email' => 'john@example.com']);
    test()->assertAuthenticated();
});

it('fails registration with duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = test()->postJson('/graphql', [
        'query' => '
            mutation Register($input: RegisterInput!) {
                register(input: $input) {
                    user { id }
                }
            }
        ',
        'variables' => [
            'input' => [
                'name' => 'Jane',
                'email' => 'taken@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
});

it('fails registration with password mismatch', function () {
    $response = test()->postJson('/graphql', [
        'query' => '
            mutation Register($input: RegisterInput!) {
                register(input: $input) {
                    user { id }
                }
            }
        ',
        'variables' => [
            'input' => [
                'name' => 'Jane',
                'email' => 'jane@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'DifferentPassword!',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
    test()->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
});

it('fails registration with missing required fields', function () {
    $response = test()->postJson('/graphql', [
        'query' => '
            mutation Register($input: RegisterInput!) {
                register(input: $input) {
                    user { id }
                }
            }
        ',
        'variables' => [
            'input' => [
                'name' => '',
                'email' => '',
                'password' => '',
                'password_confirmation' => '',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
});
