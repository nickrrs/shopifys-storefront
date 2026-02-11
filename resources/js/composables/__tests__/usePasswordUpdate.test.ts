import { describe, it, expect, vi, beforeEach } from 'vitest';

vi.mock('@/lib/graphql', () => ({
    requestWrapper: vi.fn(),
}));

import { requestWrapper } from '@/lib/graphql';
import { usePasswordUpdate } from '../usePasswordUpdate';

const mockRequest = vi.mocked(requestWrapper);

describe('usePasswordUpdate', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('returns submitting ref and updatePassword function', () => {
        const { submitting, updatePassword } = usePasswordUpdate();

        expect(submitting.value).toBe(false);
        expect(typeof updatePassword).toBe('function');
    });

    it('sends updatePassword mutation and returns true', async () => {
        mockRequest.mockResolvedValue({ updatePassword: true });

        const { updatePassword } = usePasswordUpdate();
        const result = await updatePassword({
            current_password: 'old',
            password: 'new123',
            password_confirmation: 'new123',
        });

        expect(result).toBe(true);
        expect(mockRequest).toHaveBeenCalledWith(
            expect.stringContaining('mutation UpdatePassword'),
            {
                input: {
                    current_password: 'old',
                    password: 'new123',
                    password_confirmation: 'new123',
                },
            },
        );
    });

    it('resets submitting on error', async () => {
        mockRequest.mockRejectedValue(new Error('Current password is incorrect'));

        const { updatePassword, submitting } = usePasswordUpdate();

        await expect(
            updatePassword({ current_password: 'wrong', password: 'new', password_confirmation: 'new' }),
        ).rejects.toThrow('Current password is incorrect');

        expect(submitting.value).toBe(false);
    });
});
