<?php

namespace App\GraphQL\Mutations\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateProfile
{
    public function __invoke(null $_, array $args): User
    {
        /** @var User $user */
        $user = Auth::user();

        $input = $args['input'] ?? [];

        $validated = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ])->validate();

        $user->fill($validated);
        $user->save();

        return $user;
    }
}
