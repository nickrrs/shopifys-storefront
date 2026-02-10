<?php

namespace App\GraphQL\Mutations\Auth;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class Register
{
    public function __construct(
        protected CreatesNewUsers $creator,
        protected StatefulGuard $guard,
    ) {
    }

    public function __invoke(null $_, array $args): array
    {
        $input = $args['input'] ?? [];

        $user = $this->creator->create($input);

        $this->guard->login($user);

        Log::info('Auth: user registered.', ['user_id' => $user->id]);

        return [
            'user' => $user,
        ];
    }
}
