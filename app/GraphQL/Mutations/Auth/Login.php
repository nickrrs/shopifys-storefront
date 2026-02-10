<?php

namespace App\GraphQL\Mutations\Auth;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Login
{
    public function __construct(
        protected StatefulGuard $guard,
    ) {
    }

    public function __invoke(null $_, array $args): array
    {
        $input = $args['input'] ?? [];

        $credentials = [
            'email' => $input['email'] ?? null,
            'password' => $input['password'] ?? null,
        ];

        $remember = (bool) ($input['remember'] ?? false);

        if (! $this->guard->attempt($credentials, $remember)) {
            Log::info('Auth: failed login attempt.', ['email' => $credentials['email']]);

            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $user = $this->guard->user();

        Log::info('Auth: user logged in.', ['user_id' => $user->getAuthIdentifier()]);

        return [
            'user' => $user,
        ];
    }
}
