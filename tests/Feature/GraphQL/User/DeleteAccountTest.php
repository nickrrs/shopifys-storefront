<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('deletes user account with correct password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);

    $userId = $user->id;

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation DeleteAccount($input: DeleteAccountInput!) {
                deleteAccount(input: $input)
            }
        ',
        'variables' => [
            'input' => [
                'password' => 'Password123!',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.deleteAccount', true);

    test()->assertDatabaseMissing('users', ['id' => $userId]);
    test()->assertGuest();
});

it('fails to delete account with wrong password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('CorrectPassword!'),
    ]);

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation DeleteAccount($input: DeleteAccountInput!) {
                deleteAccount(input: $input)
            }
        ',
        'variables' => [
            'input' => [
                'password' => 'WrongPassword!',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);

    test()->assertDatabaseHas('users', ['id' => $user->id]);
});

it('requires authentication', function () {
    $response = test()->postJson('/graphql', [
        'query' => '
            mutation DeleteAccount($input: DeleteAccountInput!) {
                deleteAccount(input: $input)
            }
        ',
        'variables' => [
            'input' => [
                'password' => 'x',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
});
