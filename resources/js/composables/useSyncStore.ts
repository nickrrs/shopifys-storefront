import { ref } from 'vue';
import type { StoreItem } from '@/composables/useStores';
import { requestWrapper } from '@/lib/graphql';

export function useSyncStore() {
    const syncing = ref(false);

    const syncStoreProducts = async (storeId: string): Promise<StoreItem> => {
        syncing.value = true;

        try {
            const data = await requestWrapper<{ syncStoreProducts: StoreItem }>(
                `
                    mutation SyncStoreProducts($storeId: ID!) {
                        syncStoreProducts(storeId: $storeId) {
                            id
                            name
                            shopifyDomain
                            syncing
                            connectedAt
                            createdAt
                        }
                    }
                `,
                { storeId },
            );

            return data.syncStoreProducts;
        } finally {
            syncing.value = false;
        }
    };

    return { syncing, syncStoreProducts };
}
