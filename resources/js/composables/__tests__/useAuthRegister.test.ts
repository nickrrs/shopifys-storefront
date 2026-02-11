import { describe, it, expect, vi, beforeEach } from 'vitest';

vi.mock('@/lib/graphql', () => ({
    requestWrapper: vi.fn(),
}));

import { requestWrapper } from '@/lib/graphql';
import { useAuthRegister } from '../useAuthRegister';

const mockRequest = vi.mocked(requestWrapper);

describe('useAuthRegister', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('returns submitting ref and register function', () => {
        const { submitting, register } = useAuthRegister();

        expect(submitting.value).toBe(false);
        expect(typeof register).toBe('function');
    });

    it('sends register mutation and returns user', async () => {
        const mockUser = { id: '1', name: 'Jane', email: 'jane@test.com' };

        mockRequest.mockResolvedValue({ register: { user: mockUser } });

        const { register } = useAuthRegister();
        const result = await register({
            name: 'Jane',
            email: 'jane@test.com',
            password: 'Password123!',
            password_confirmation: 'Password123!',
        });

        expect(result).toEqual(mockUser);
        expect(mockRequest).toHaveBeenCalledWith(
            expect.stringContaining('mutation Register'),
            {
                input: {
                    name: 'Jane',
                    email: 'jane@test.com',
                    password: 'Password123!',
                    password_confirmation: 'Password123!',
                },
            },
        );
    });

    it('resets submitting to false on error', async () => {
        mockRequest.mockRejectedValue(new Error('Email already taken'));

        const { register, submitting } = useAuthRegister();

        await expect(
            register({ name: 'A', email: 'a@b.com', password: 'p', password_confirmation: 'p' }),
        ).rejects.toThrow('Email already taken');

        expect(submitting.value).toBe(false);
    });

    it('throws when response has no user', async () => {
        mockRequest.mockResolvedValue({ register: { user: null } });

        const { register } = useAuthRegister();

        await expect(
            register({ name: 'A', email: 'a@b.com', password: 'p', password_confirmation: 'p' }),
        ).rejects.toThrow('Invalid registration response');
    });
});
