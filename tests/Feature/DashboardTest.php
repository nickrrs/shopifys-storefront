<?php

use App\Models\User;

test('guests are redirected to the home page', function () {
    $response = test()->get(route('dashboard'));
    $response->assertRedirect(route('home'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    test()->actingAs($user);

    $response = test()->get(route('dashboard'));
    $response->assertOk();
});
