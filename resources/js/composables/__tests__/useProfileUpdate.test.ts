import { describe, it, expect, vi, beforeEach } from 'vitest';

vi.mock('@/lib/graphql', () => ({
    requestWrapper: vi.fn(),
}));

import { requestWrapper } from '@/lib/graphql';
import { useProfileUpdate } from '../useProfileUpdate';

const mockRequest = vi.mocked(requestWrapper);

describe('useProfileUpdate', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('returns submitting ref and updateProfile function', () => {
        const { submitting, updateProfile } = useProfileUpdate();

        expect(submitting.value).toBe(false);
        expect(typeof updateProfile).toBe('function');
    });

    it('sends updateProfile mutation and returns updated user', async () => {
        const mockUser = { id: '1', name: 'Updated', email: 'updated@test.com' };

        mockRequest.mockResolvedValue({ updateProfile: mockUser });

        const { updateProfile } = useProfileUpdate();
        const result = await updateProfile({ name: 'Updated', email: 'updated@test.com' });

        expect(result).toEqual(mockUser);
        expect(mockRequest).toHaveBeenCalledWith(
            expect.stringContaining('mutation UpdateProfile'),
            { input: { name: 'Updated', email: 'updated@test.com' } },
        );
    });

    it('resets submitting on error', async () => {
        mockRequest.mockRejectedValue(new Error('Email already taken'));

        const { updateProfile, submitting } = useProfileUpdate();

        await expect(updateProfile({ name: 'A', email: 'dup@test.com' })).rejects.toThrow('Email already taken');
        expect(submitting.value).toBe(false);
    });
});
