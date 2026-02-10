<?php

namespace App\GraphQL\Mutations\Shopify;

use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UpdateStore
{
    public function __invoke(null $_, array $args): Store
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Store|null $store */
        $store = $user->stores()->find($args['id']);

        if (! $store) {
            throw ValidationException::withMessages([
                'id' => ['Store not found.'],
            ]);
        }

        $input = $args['input'] ?? [];

        $store->update([
            'name' => (string) ($input['name'] ?? $store->name),
        ]);

        Log::info('UpdateStore: store updated.', ['store_id' => $store->id, 'user_id' => $user->id]);

        return $store->fresh();
    }
}
