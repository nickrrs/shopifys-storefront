import { ref } from 'vue';
import { requestWrapper } from '@/lib/graphql';
import type { StoreItem } from '@/composables/useStores';

type ConnectStoreInput = {
    name: string;
    shopifyDomain: string;
    accessToken: string;
};

export function useConnectStore() {
    const submitting = ref(false);

    const connectStore = async (input: ConnectStoreInput): Promise<StoreItem> => {
        submitting.value = true;

        try {
            const data = await requestWrapper<{ connectStore: StoreItem }>(
                `
                    mutation ConnectStore($input: ConnectStoreInput!) {
                        connectStore(input: $input) {
                            id
                            name
                            shopifyDomain
                            connectedAt
                            createdAt
                        }
                    }
                `,
                { input },
            );

            const store = data.connectStore;

            if (!store) {
                throw new Error('Invalid response when connecting store.');
            }

            return store;
        } finally {
            submitting.value = false;
        }
    };

    return { submitting, connectStore };
}
