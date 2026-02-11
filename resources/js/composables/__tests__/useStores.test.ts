import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useStores } from '../useStores';
import type { StoreItem } from '../useStores';

vi.mock('@/lib/graphql', () => ({
    requestWrapper: vi.fn(),
}));

import { requestWrapper } from '@/lib/graphql';

const mockRequest = vi.mocked(requestWrapper);

function createMockStore(overrides: Partial<StoreItem> = {}): StoreItem {
    return {
        id: '1',
        name: 'Test Store',
        shopifyDomain: 'test.myshopify.com',
        syncing: false,
        productsCount: 2,
        connectedAt: '2026-02-10',
        createdAt: '2026-02-10',
        products: {
            edges: [
                { cursor: 'c1', node: { id: 'p1', title: 'Product 1', description: null, price: 10, inventoryQuantity: 5, status: 'ACTIVE' } },
                { cursor: 'c2', node: { id: 'p2', title: 'Product 2', description: null, price: 20, inventoryQuantity: 3, status: 'DRAFT' } },
            ],
            pageInfo: { hasNextPage: false, endCursor: 'c2' },
        },
        ...overrides,
    };
}

describe('useStores', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('returns reactive stores, loading, and functions', () => {
        const { stores, loading, fetchStores, loadMoreProducts, setStoreSyncing } = useStores();

        expect(stores.value).toEqual([]);
        expect(loading.value).toBe(false);
        expect(typeof fetchStores).toBe('function');
        expect(typeof loadMoreProducts).toBe('function');
        expect(typeof setStoreSyncing).toBe('function');
    });

    it('fetchStores sets loading on initial load and populates stores', async () => {
        const mockStore = createMockStore();

        mockRequest.mockResolvedValue({ myStores: [mockStore] });

        const { stores, loading, fetchStores } = useStores();

        const promise = fetchStores();
        expect(loading.value).toBe(true);

        await promise;

        expect(loading.value).toBe(false);
        expect(stores.value).toHaveLength(1);
        expect(stores.value[0].name).toBe('Test Store');
    });

    it('fetchStores does not set loading on subsequent calls', async () => {
        const mockStore = createMockStore();

        mockRequest.mockResolvedValue({ myStores: [mockStore] });

        const { stores, loading, fetchStores } = useStores();

        await fetchStores();
        expect(stores.value).toHaveLength(1);

        mockRequest.mockResolvedValue({ myStores: [createMockStore({ name: 'Updated' })] });

        const promise = fetchStores();
        expect(loading.value).toBe(false);

        await promise;

        expect(stores.value[0].name).toBe('Updated');
    });

    it('fetchStores handles empty response', async () => {
        mockRequest.mockResolvedValue({ myStores: null });

        const { stores, fetchStores } = useStores();
        await fetchStores();

        expect(stores.value).toEqual([]);
    });

    it('loadMoreProducts appends new products to existing store', async () => {
        const mockStore = createMockStore({
            products: {
                edges: [
                    { cursor: 'c1', node: { id: 'p1', title: 'Product 1', description: null, price: 10, inventoryQuantity: 5, status: 'ACTIVE' } },
                ],
                pageInfo: { hasNextPage: true, endCursor: 'c1' },
            },
        });

        mockRequest.mockResolvedValueOnce({ myStores: [mockStore] });

        const { stores, fetchStores, loadMoreProducts } = useStores();
        await fetchStores();

        expect(stores.value[0].products.edges).toHaveLength(1);

        const moreProducts = {
            edges: [
                { cursor: 'c2', node: { id: 'p2', title: 'Product 2', description: null, price: 20, inventoryQuantity: 3, status: 'DRAFT' } },
            ],
            pageInfo: { hasNextPage: false, endCursor: 'c2' },
        };

        mockRequest.mockResolvedValueOnce({ myStores: [{ id: '1', products: moreProducts }] });

        await loadMoreProducts('1', 'c1');

        expect(stores.value[0].products.edges).toHaveLength(2);
        expect(stores.value[0].products.edges[1].node.title).toBe('Product 2');
        expect(stores.value[0].products.pageInfo.hasNextPage).toBe(false);
    });

    it('loadMoreProducts does nothing for unknown store', async () => {
        mockRequest.mockResolvedValueOnce({ myStores: [createMockStore()] });

        const { stores, fetchStores, loadMoreProducts } = useStores();
        await fetchStores();

        mockRequest.mockResolvedValueOnce({ myStores: [] });

        await loadMoreProducts('999', 'cursor');

        expect(stores.value[0].products.edges).toHaveLength(2);
    });

    it('setStoreSyncing updates store syncing status', async () => {
        mockRequest.mockResolvedValue({ myStores: [createMockStore()] });

        const { stores, fetchStores, setStoreSyncing } = useStores();
        await fetchStores();

        expect(stores.value[0].syncing).toBe(false);

        setStoreSyncing('1', true);

        expect(stores.value[0].syncing).toBe(true);

        setStoreSyncing('1', false);

        expect(stores.value[0].syncing).toBe(false);
    });

    it('setStoreSyncing does nothing for unknown store', async () => {
        mockRequest.mockResolvedValue({ myStores: [createMockStore()] });

        const { stores, fetchStores, setStoreSyncing } = useStores();
        await fetchStores();

        setStoreSyncing('999', true);

        expect(stores.value[0].syncing).toBe(false);
    });
});
