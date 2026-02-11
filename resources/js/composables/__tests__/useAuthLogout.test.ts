import { describe, it, expect, vi, beforeEach } from 'vitest';

vi.mock('@/lib/graphql', () => ({
    requestWrapper: vi.fn(),
}));

import { requestWrapper } from '@/lib/graphql';
import { useAuthLogout } from '../useAuthLogout';

const mockRequest = vi.mocked(requestWrapper);

describe('useAuthLogout', () => {
    beforeEach(() => {
        vi.clearAllMocks();

        Object.defineProperty(window, 'location', {
            value: { href: '' },
            writable: true,
        });
    });

    it('returns submitting ref and logout function', () => {
        const { submitting, logout } = useAuthLogout();

        expect(submitting.value).toBe(false);
        expect(typeof logout).toBe('function');
    });

    it('sends logout mutation and redirects to /', async () => {
        mockRequest.mockResolvedValue({ logout: true });

        const { logout } = useAuthLogout();
        await logout();

        expect(mockRequest).toHaveBeenCalledWith(expect.stringContaining('mutation Logout'));
        expect(window.location.href).toBe('/');
    });

    it('prevents duplicate logout calls', async () => {
        let resolvePromise: (value: unknown) => void;
        mockRequest.mockImplementation(() => new Promise((r) => { resolvePromise = r; }));

        const { logout, submitting } = useAuthLogout();

        const first = logout();
        expect(submitting.value).toBe(true);

        logout(); // second call should be ignored

        resolvePromise!({ logout: true });
        await first;

        expect(mockRequest).toHaveBeenCalledTimes(1);
    });

    it('resets submitting on error', async () => {
        mockRequest.mockRejectedValue(new Error('Network error'));

        const { logout, submitting } = useAuthLogout();

        await expect(logout()).rejects.toThrow('Network error');
        expect(submitting.value).toBe(false);
    });
});
