<?php

test('registration screen can be rendered', function () {
    $response = test()->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = test()->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    test()->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
