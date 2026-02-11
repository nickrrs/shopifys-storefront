<?php

namespace App\Jobs;

use App\Contracts\IntegrationClient;
use App\Contracts\ProductMapper;
use App\Models\Product;
use App\Models\Store;
use App\Services\Shopify\ShopifyGraphqlClientFactory;
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
    ) {}

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
        /** @var ShopifyGraphqlClientFactory $clientFactory */
        $clientFactory = app(ShopifyGraphqlClientFactory::class);

        /** @var ProductMapper $mapper */
        $mapper = app(ProductMapper::class);

        $client = $clientFactory->create($store);

        return $this->syncProductsWithClient($store, $client, $mapper);
    }

    protected function syncProductsWithClient(Store $store, IntegrationClient $client, ProductMapper $mapper): int
    {
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

                $product = $mapper->fromShopifyNode($node);

                Product::updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'shopify_product_id' => $node['id'],
                    ],
                    [
                        'store_id' => $store->id,
                        'title' => $product->title,
                        'description' => $product->description,
                        'price' => $product->price,
                        'inventory_quantity' => $product->inventory_quantity,
                        'status' => $product->status,
                    ],
                );

                $totalSynced++;
            }

            $hasNextPage = $products['pageInfo']['hasNextPage'] ?? false;
        }

        return $totalSynced;
    }
}
