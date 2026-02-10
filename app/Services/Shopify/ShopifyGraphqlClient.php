<?php

namespace App\Services\Shopify;

use App\Contracts\IntegrationClient;
use App\Models\Store;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ShopifyGraphqlClient implements IntegrationClient
{
    public function __construct(
        protected Store $store,
        protected ?string $apiVersion = null,
    ) {
        $this->apiVersion = $apiVersion ?: config('services.shopify.api_version', '2026-01');
    }

    protected function endpoint(): string
    {
        $domain = rtrim($this->store->shopify_domain, '/');

        return "https://{$domain}/admin/api/{$this->apiVersion}/graphql.json";
    }

    public function request(string $query, array $variables = []): array
    {
        $payload = ['query' => $query];

        if (! empty($variables)) {
            $payload['variables'] = (object) $variables;
        }

        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token' => $this->store->access_token,
            ])->post($this->endpoint(), $payload);

            return $this->handleResponse($response);
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('ShopifyGraphqlClient: request failed.', [
                'store_id' => $this->store->id ?? null,
                'endpoint' => $this->endpoint(),
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException($this->getErrorMessage());
        }
    }

    protected function handleResponse(Response $response): array
    {
        if (! $response->successful()) {
            Log::warning('ShopifyGraphqlClient: HTTP error.', [
                'store_id' => $this->store->id ?? null,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException($this->getErrorMessage());
        }

        $data = $response->json();

        if (! empty($data['errors'])) {
            Log::warning('ShopifyGraphqlClient: GraphQL errors.', [
                'store_id' => $this->store->id ?? null,
                'errors' => $data['errors'],
            ]);

            throw new RuntimeException($this->getErrorMessage());
        }

        return $data['data'] ?? [];
    }

    private function getErrorMessage(): string
    {
        return 'An error occurred while making the request to Shopify. Please contact support.';
    }
}
