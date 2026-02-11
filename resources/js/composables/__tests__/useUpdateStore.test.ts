import { describe, it, expect, vi, beforeEach } from 'vitest';

vi.mock('@/lib/graphql', () => ({
    requestWrapper: vi.fn(),
}));

import { requestWrapper } from '@/lib/graphql';
import { useUpdateStore } from '../useUpdateStore';

const mockRequest = vi.mocked(requestWrapper);

describe('useUpdateStore', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('returns submitting ref and updateStore function', () => {
        const { submitting, updateStore } = useUpdateStore();

        expect(submitting.value).toBe(false);
        expect(typeof updateStore).toBe('function');
    });

    it('sends updateStore mutation with id and input', async () => {
        const mockStore = {
            id: '5',
            name: 'Renamed Store',
            shopifyDomain: 'store.myshopify.com',
            syncing: false,
            connectedAt: '2026-01-01',
            createdAt: '2026-01-01',
        };

        mockRequest.mockResolvedValue({ updateStore: mockStore });

        const { updateStore } = useUpdateStore();
        const result = await updateStore('5', { name: 'Renamed Store' });

        expect(result).toEqual(mockStore);
        expect(mockRequest).toHaveBeenCalledWith(
            expect.stringContaining('mutation UpdateStore'),
            { id: '5', input: { name: 'Renamed Store' } },
        );
    });

    it('resets submitting on error', async () => {
        mockRequest.mockRejectedValue(new Error('Store not found'));

        const { updateStore, submitting } = useUpdateStore();

        await expect(updateStore('999', { name: 'A' })).rejects.toThrow('Store not found');
        expect(submitting.value).toBe(false);
    });
});
