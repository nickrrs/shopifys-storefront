import { ref } from 'vue';
import { requestWrapper } from '@/lib/graphql';
import type { StoreItem } from '@/composables/useStores';

type UpdateStoreInput = {
    name: string;
};

export function useUpdateStore() {
    const submitting = ref(false);

    const updateStore = async (id: string, input: UpdateStoreInput): Promise<StoreItem> => {
        submitting.value = true;

        try {
            const data = await requestWrapper<{ updateStore: StoreItem }>(
                `
                    mutation UpdateStore($id: ID!, $input: UpdateStoreInput!) {
                        updateStore(id: $id, input: $input) {
                            id
                            name
                            shopifyDomain
                            syncing
                            connectedAt
                            createdAt
                        }
                    }
                `,
                { id, input },
            );

            return data.updateStore;
        } finally {
            submitting.value = false;
        }
    };

    return { submitting, updateStore };
}
