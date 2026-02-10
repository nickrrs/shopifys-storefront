<?php

namespace App\GraphQL\Mutations\Shopify;

use App\Models\Store;
use App\Models\User;
use App\Services\Shopify\ShopifyStoreConnector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ConnectStore
{
    public function __construct(protected ShopifyStoreConnector $connector)
    {
    }

    public function __invoke(null $_, array $args): Store
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var array<string,mixed> $input */
        $input = $args['input'] ?? [];

        $name = trim((string) ($input['name'] ?? ''));
        $shopifyDomain = trim((string) ($input['shopifyDomain'] ?? ''));
        $accessToken = trim((string) ($input['accessToken'] ?? ''));

        if ($name === '' || $shopifyDomain === '' || $accessToken === '') {
            throw ValidationException::withMessages([
                'input' => ['All fields (name, shopifyDomain, accessToken) are required.'],
            ]);
        }

        try {
            return $this->connector->connect($user, $name, $shopifyDomain, $accessToken);
        } catch (\Throwable $e) {
            Log::error('ConnectStore: failed.', [
                'user_id' => $user->id,
                'shopify_domain' => $shopifyDomain,
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'shopifyDomain' => [$e->getMessage()],
            ]);
        }
    }
}
