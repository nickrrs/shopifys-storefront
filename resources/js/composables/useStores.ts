import { ref } from 'vue';
import { requestWrapper } from '@/lib/graphql';

export type ProductItem = {
    id: string;
    title: string;
    description: string | null;
    price: number;
    inventoryQuantity: number | null;
    status: string;
};

export type PageInfo = {
    hasNextPage: boolean;
    endCursor: string | null;
};

export type ProductConnection = {
    edges: { cursor: string; node: ProductItem }[];
    pageInfo: PageInfo;
};

export type StoreItem = {
    id: string;
    name: string;
    shopifyDomain: string;
    syncing: boolean;
    productsCount: number;
    connectedAt: string;
    createdAt: string;
    products: ProductConnection;
};

const PRODUCTS_PER_PAGE = 10;

export function useStores() {
    const stores = ref<StoreItem[]>([]);
    const loading = ref(false);

    const fetchStores = async () => {
        const isInitialLoad = stores.value.length === 0;

        if (isInitialLoad) {
            loading.value = true;
        }

        try {
            const data = await requestWrapper<{ myStores: StoreItem[] }>(
                `
                    query MyStores($productsFirst: Int!) {
                        myStores {
                            id
                            name
                            shopifyDomain
                            syncing
                            productsCount
                            connectedAt
                            createdAt
                            products(first: $productsFirst) {
                                edges {
                                    cursor
                                    node {
                                        id
                                        title
                                        description
                                        price
                                        inventoryQuantity
                                        status
                                    }
                                }
                                pageInfo {
                                    hasNextPage
                                    endCursor
                                }
                            }
                        }
                    }
                `,
                { productsFirst: PRODUCTS_PER_PAGE },
            );

            stores.value = data.myStores ?? [];
        } finally {
            loading.value = false;
        }
    };

    const loadMoreProducts = async (storeId: string, after: string) => {
        const data = await requestWrapper<{ myStores: StoreItem[] }>(
            `
                query MyStores($productsFirst: Int!, $productsAfter: String) {
                    myStores {
                        id
                        products(first: $productsFirst, after: $productsAfter) {
                            edges {
                                cursor
                                node {
                                    id
                                    title
                                    description
                                    price
                                    inventoryQuantity
                                    status
                                }
                            }
                            pageInfo {
                                hasNextPage
                                endCursor
                            }
                        }
                    }
                }
            `,
            { productsFirst: PRODUCTS_PER_PAGE, productsAfter: after },
        );

        const updatedStores = data.myStores ?? [];
        const updatedStore = updatedStores.find((s) => s.id === storeId);

        if (!updatedStore) return;

        const storeIndex = stores.value.findIndex((s) => s.id === storeId);

        if (storeIndex === -1) return;

        const existing = stores.value[storeIndex];

        stores.value[storeIndex] = {
            ...existing,
            products: {
                edges: [...existing.products.edges, ...updatedStore.products.edges],
                pageInfo: updatedStore.products.pageInfo,
            },
        };
    };

    const setStoreSyncing = (storeId: string, syncing: boolean) => {
        const index = stores.value.findIndex((s) => s.id === storeId);

        if (index !== -1) {
            stores.value[index] = { ...stores.value[index], syncing };
        }
    };

    return { stores, loading, fetchStores, loadMoreProducts, setStoreSyncing, PRODUCTS_PER_PAGE };
}
