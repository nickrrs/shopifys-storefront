import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useConnectStore } from '../useConnectStore';

vi.mock('@/lib/graphql', () => ({
    requestWrapper: vi.fn(),
}));

import { requestWrapper } from '@/lib/graphql';

const mockRequest = vi.mocked(requestWrapper);

describe('useConnectStore', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('returns submitting ref and connectStore function', () => {
        const { submitting, connectStore } = useConnectStore();

        expect(submitting.value).toBe(false);
        expect(typeof connectStore).toBe('function');
    });

    it('sends connectStore mutation and returns store', async () => {
        const mockStore = {
            id: '1',
            name: 'My Store',
            shopifyDomain: 'my-store.myshopify.com',
            connectedAt: '2026-02-10',
            createdAt: '2026-02-10',
        };

        mockRequest.mockResolvedValue({ connectStore: mockStore });

        const { connectStore } = useConnectStore();
        const result = await connectStore({
            name: 'My Store',
            shopifyDomain: 'my-store.myshopify.com',
            accessToken: 'shpat_xxx',
        });

        expect(result).toEqual(mockStore);
        expect(mockRequest).toHaveBeenCalledWith(
            expect.stringContaining('mutation ConnectStore'),
            {
                input: {
                    name: 'My Store',
                    shopifyDomain: 'my-store.myshopify.com',
                    accessToken: 'shpat_xxx',
                },
            },
        );
    });

    it('throws when response has no store', async () => {
        mockRequest.mockResolvedValue({ connectStore: null });

        const { connectStore } = useConnectStore();

        await expect(
            connectStore({ name: 'A', shopifyDomain: 'a.myshopify.com', accessToken: 'x' }),
        ).rejects.toThrow('Invalid response when connecting store');
    });

    it('resets submitting on error', async () => {
        mockRequest.mockRejectedValue(new Error('Invalid access token'));

        const { connectStore, submitting } = useConnectStore();

        await expect(
            connectStore({ name: 'A', shopifyDomain: 'a.myshopify.com', accessToken: 'bad' }),
        ).rejects.toThrow('Invalid access token');

        expect(submitting.value).toBe(false);
    });
});
