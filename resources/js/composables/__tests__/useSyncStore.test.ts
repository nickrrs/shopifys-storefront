import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useSyncStore } from '../useSyncStore';

vi.mock('@/lib/graphql', () => ({
    requestWrapper: vi.fn(),
}));

import { requestWrapper } from '@/lib/graphql';

const mockRequest = vi.mocked(requestWrapper);

describe('useSyncStore', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('returns syncing ref and syncStoreProducts function', () => {
        const { syncing, syncStoreProducts } = useSyncStore();

        expect(syncing.value).toBe(false);
        expect(typeof syncStoreProducts).toBe('function');
    });

    it('sends syncStoreProducts mutation with storeId', async () => {
        const mockStore = {
            id: '3',
            name: 'Test Store',
            shopifyDomain: 'test.myshopify.com',
            syncing: true,
            connectedAt: '2026-02-10',
            createdAt: '2026-02-10',
        };

        mockRequest.mockResolvedValue({ syncStoreProducts: mockStore });

        const { syncStoreProducts } = useSyncStore();
        const result = await syncStoreProducts('3');

        expect(result).toEqual(mockStore);
        expect(mockRequest).toHaveBeenCalledWith(
            expect.stringContaining('mutation SyncStoreProducts'),
            { storeId: '3' },
        );
    });

    it('resets syncing on error', async () => {
        mockRequest.mockRejectedValue(new Error('Sync failed'));

        const { syncStoreProducts, syncing } = useSyncStore();

        await expect(syncStoreProducts('1')).rejects.toThrow('Sync failed');
        expect(syncing.value).toBe(false);
    });
});
