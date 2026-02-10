<?php

namespace App\GraphQL\Mutations\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UpdatePassword
{
    public function __invoke(null $_, array $args): bool
    {
        /** @var User $user */
        $user = Auth::user();

        $input = $args['input'] ?? [];

        $validated = Validator::make($input, [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
        ])->validate();

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return true;
    }
}
