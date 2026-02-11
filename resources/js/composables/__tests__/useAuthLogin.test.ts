import { describe, it, expect, vi, beforeEach } from 'vitest';

vi.mock('@/lib/graphql', () => ({
    requestWrapper: vi.fn(),
}));

import { requestWrapper } from '@/lib/graphql';
import { useAuthLogin } from '../useAuthLogin';

const mockRequest = vi.mocked(requestWrapper);

describe('useAuthLogin', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('returns submitting ref and login function', () => {
        const { submitting, login } = useAuthLogin();

        expect(submitting.value).toBe(false);
        expect(typeof login).toBe('function');
    });

    it('sends login mutation and returns user', async () => {
        const mockUser = { id: '1', name: 'John', email: 'john@test.com' };

        mockRequest.mockResolvedValue({ login: { user: mockUser } });

        const { login, submitting } = useAuthLogin();
        const result = await login({ email: 'john@test.com', password: 'secret' });

        expect(result).toEqual(mockUser);
        expect(submitting.value).toBe(false);
        expect(mockRequest).toHaveBeenCalledWith(
            expect.stringContaining('mutation Login'),
            { input: { email: 'john@test.com', password: 'secret' } },
        );
    });

    it('manages submitting state during request', async () => {
        const states: boolean[] = [];

        mockRequest.mockImplementation(async () => {
            return new Promise((resolve) => {
                setTimeout(() => resolve({ login: { user: { id: '1', name: 'A', email: 'a@b.com' } } }), 10);
            });
        });

        const { login, submitting } = useAuthLogin();
        const promise = login({ email: 'a@b.com', password: 'pass' });

        states.push(submitting.value);
        await promise;
        states.push(submitting.value);

        expect(states).toEqual([true, false]);
    });

    it('resets submitting to false on error', async () => {
        mockRequest.mockRejectedValue(new Error('Invalid credentials'));

        const { login, submitting } = useAuthLogin();

        await expect(login({ email: 'a@b.com', password: 'wrong' })).rejects.toThrow('Invalid credentials');
        expect(submitting.value).toBe(false);
    });

    it('throws when response has no user', async () => {
        mockRequest.mockResolvedValue({ login: { user: null } });

        const { login } = useAuthLogin();

        await expect(login({ email: 'a@b.com', password: 'pass' })).rejects.toThrow('Invalid authentication response');
    });
});
