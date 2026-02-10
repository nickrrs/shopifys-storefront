<?php

namespace App\GraphQL\Mutations\User;

use App\Models\User;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeleteAccount
{
    public function __construct(
        protected StatefulGuard $guard,
        protected Request $request,
    ) {
    }

    public function __invoke(null $_, array $args): bool
    {
        /** @var User $user */
        $user = $this->guard->user();

        $input = $args['input'] ?? [];

        Validator::make($input, [
            'password' => ['required', 'string', 'current_password'],
        ])->validate();

        $this->guard->logout();

        $user->delete();

        $this->request->session()->invalidate();
        $this->request->session()->regenerateToken();

        return true;
    }
}
