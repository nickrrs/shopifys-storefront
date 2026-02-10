<?php

namespace App\GraphQL\Mutations\Auth;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Logout
{
    public function __construct(
        protected StatefulGuard $guard,
        protected Request $request,
    ) {
    }

    public function __invoke(null $_, array $args): bool
    {
        $userId = $this->guard->user()?->getAuthIdentifier();

        $this->guard->logout();

        $this->request->session()->invalidate();
        $this->request->session()->regenerateToken();

        Log::info('Auth: user logged out.', ['user_id' => $userId]);

        return true;
    }
}
