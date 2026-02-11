import { describe, it, expect, vi, beforeEach } from 'vitest';

vi.mock('@/lib/graphql', () => ({
    requestWrapper: vi.fn(),
}));

import { requestWrapper } from '@/lib/graphql';
import { useDeleteAccount } from '../useDeleteAccount';

const mockRequest = vi.mocked(requestWrapper);

describe('useDeleteAccount', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('returns submitting ref and deleteAccount function', () => {
        const { submitting, deleteAccount } = useDeleteAccount();

        expect(submitting.value).toBe(false);
        expect(typeof deleteAccount).toBe('function');
    });

    it('sends deleteAccount mutation with password', async () => {
        mockRequest.mockResolvedValue({ deleteAccount: true });

        const { deleteAccount } = useDeleteAccount();
        const result = await deleteAccount('MyPassword123');

        expect(result).toBe(true);
        expect(mockRequest).toHaveBeenCalledWith(
            expect.stringContaining('mutation DeleteAccount'),
            { input: { password: 'MyPassword123' } },
        );
    });

    it('resets submitting on error', async () => {
        mockRequest.mockRejectedValue(new Error('Password is incorrect'));

        const { deleteAccount, submitting } = useDeleteAccount();

        await expect(deleteAccount('wrong')).rejects.toThrow('Password is incorrect');
        expect(submitting.value).toBe(false);
    });
});
