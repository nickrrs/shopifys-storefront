<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('updates user password with correct current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword123!'),
    ]);

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation UpdatePassword($input: UpdatePasswordInput!) {
                updatePassword(input: $input)
            }
        ',
        'variables' => [
            'input' => [
                'current_password' => 'OldPassword123!',
                'password' => 'NewPassword456!',
                'password_confirmation' => 'NewPassword456!',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.updatePassword', true);

    // Verify the new password works
    expect(Hash::check('NewPassword456!', $user->fresh()->password))->toBeTrue();
});

it('fails with wrong current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('CorrectPassword!'),
    ]);

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation UpdatePassword($input: UpdatePasswordInput!) {
                updatePassword(input: $input)
            }
        ',
        'variables' => [
            'input' => [
                'current_password' => 'WrongPassword!',
                'password' => 'NewPassword456!',
                'password_confirmation' => 'NewPassword456!',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
});

it('fails when password confirmation does not match', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword123!'),
    ]);

    $response = test()->actingAs($user)->postJson('/graphql', [
        'query' => '
            mutation UpdatePassword($input: UpdatePasswordInput!) {
                updatePassword(input: $input)
            }
        ',
        'variables' => [
            'input' => [
                'current_password' => 'OldPassword123!',
                'password' => 'NewPassword456!',
                'password_confirmation' => 'Mismatch!',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
});

it('requires authentication', function () {
    $response = test()->postJson('/graphql', [
        'query' => '
            mutation UpdatePassword($input: UpdatePasswordInput!) {
                updatePassword(input: $input)
            }
        ',
        'variables' => [
            'input' => [
                'current_password' => 'x',
                'password' => 'y',
                'password_confirmation' => 'y',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['errors']);
});
