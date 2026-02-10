<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Store;
use App\Services\Shopify\ShopifyGraphqlClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class SyncProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $storeId,
    ) {
    }

    public function handle(): void
    {
        /** @var Store|null $store */
        $store = Store::find($this->storeId);

        if (! $store) {
            Log::warning('SyncProductsJob: store not found.', ['store_id' => $this->storeId]);

            return;
        }

        Log::info('SyncProductsJob: started.', ['store_id' => $store->id, 'store_name' => $store->name]);

        try {
            $count = $this->syncProducts($store);

            Log::info('SyncProductsJob: completed.', [
                'store_id' => $store->id,
                'products_synced' => $count,
            ]);
        } catch (\Throwable $e) {
            Log::error('SyncProductsJob: failed.', [
                'store_id' => $store->id,
                'error' => $e->getMessage(),
            ]);
        } finally {
            $store->update(['syncing' => false]);
        }
    }

    protected function syncProducts(Store $store): int
    {
        $client = new ShopifyGraphqlClient($store);
        $cursor = null;
        $hasNextPage = true;
        $totalSynced = 0;

        $query = <<<'GRAPHQL'
            query GetProducts($first: Int!, $after: String) {
                products(first: $first, after: $after) {
                    edges {
                        cursor
                        node {
                            id
                            title
                            descriptionHtml
                            status
                            variants(first: 1) {
                                edges {
                                    node {
                                        price
                                        inventoryQuantity
                                    }
                                }
                            }
                        }
                    }
                    pageInfo {
                        hasNextPage
                    }
                }
            }
        GRAPHQL;

        while ($hasNextPage) {
            $response = $client->request($query, [
                'first' => 50,
                'after' => $cursor,
            ]);

            $products = Arr::get($response, 'products');

            if (! $products) {
                break;
            }

            $edges = $products['edges'] ?? [];

            foreach ($edges as $edge) {
                $node = $edge['node'];
                $cursor = $edge['cursor'];

                $variant = $node['variants']['edges'][0]['node'] ?? null;

                Product::updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'shopify_product_id' => $node['id'],
                    ],
                    [
                        'title' => $node['title'],
                        'description' => $node['descriptionHtml'] ?? null,
                        'price' => (float) ($variant['price'] ?? 0),
                        'inventory_quantity' => $variant['inventoryQuantity'] ?? null,
                        'status' => strtolower($node['status'] ?? 'draft'),
                    ],
                );

                $totalSynced++;
            }

            $hasNextPage = $products['pageInfo']['hasNextPage'] ?? false;
        }

        return $totalSynced;
    }
}
