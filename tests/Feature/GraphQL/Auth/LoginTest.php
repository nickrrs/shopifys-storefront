<?php

use App\Models\User;

it('logs in a user with valid credentials', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('Password123!'),
    ]);

    $response = test()->postJson('/graphql', [
        'query' => '
            mutation Login($input: LoginInput!) {
                login(input: $input) {
                    user {
                        id
                        email
                    }
                }
            }
        ',
        'variables' => [
            'input' => [
                'email' => 'user@example.com',
                'password' => 'Password123!',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.login.user.email', 'user@example.com');
    test()->assertAuthenticated();
});

it('fails login with wrong password', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('Password123!'),
    ]);

    $response = test()->postJson('/graphql', [
        'query' => '
            mutation Login($input: LoginInput!) {
                login(input: $input) {
                    user { id }
                }
            }
        ',
        'variables' => [
            'input' => [
                'email' => 'user@example.com',
                'password' => 'WrongPassword!',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
    test()->assertGuest();
});
