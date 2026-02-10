<?php

namespace App\GraphQL\Mutations\Shopify;

use App\Jobs\SyncProductsJob;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SyncStoreProducts
{
    public function __invoke(null $_, array $args): Store
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Store|null $store */
        $store = $user->stores()->find($args['storeId']);

        if (! $store) {
            throw ValidationException::withMessages([
                'storeId' => ['Store not found.'],
            ]);
        }

        if ($store->syncing) {
            throw ValidationException::withMessages([
                'storeId' => ['This store is already syncing.'],
            ]);
        }

        $store->update(['syncing' => true]);

        SyncProductsJob::dispatch($store->id);

        Log::info('SyncStoreProducts: sync dispatched.', ['store_id' => $store->id, 'user_id' => $user->id]);

        return $store->fresh();
    }
}
