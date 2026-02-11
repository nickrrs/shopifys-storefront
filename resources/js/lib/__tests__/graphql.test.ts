import { describe, it, expect, vi, beforeEach } from 'vitest';
import { requestWrapper } from '../graphql';

describe('requestWrapper', () => {
    beforeEach(() => {
        vi.restoreAllMocks();
        Object.defineProperty(document, 'cookie', { value: '', writable: true });
    });

    it('sends a POST request with query and variables', async () => {
        const mockData = { login: { user: { id: '1' } } };

        vi.spyOn(globalThis, 'fetch').mockResolvedValue({
            ok: true,
            json: async () => ({ data: mockData }),
        } as Response);

        const result = await requestWrapper('mutation Login { login { user { id } } }', { input: { email: 'a@b.com' } });

        expect(fetch).toHaveBeenCalledWith('/graphql', expect.objectContaining({
            method: 'POST',
            body: expect.stringContaining('Login'),
        }));
        expect(result).toEqual(mockData);
    });

    it('includes XSRF-TOKEN from cookie when present', async () => {
        document.cookie = 'XSRF-TOKEN=abc123';

        vi.spyOn(globalThis, 'fetch').mockResolvedValue({
            ok: true,
            json: async () => ({ data: { ok: true } }),
        } as Response);

        await requestWrapper('query { ok }');

        const call = vi.mocked(fetch).mock.calls[0];
        const headers = call[1]?.headers as Record<string, string>;

        expect(headers['X-XSRF-TOKEN']).toBe('abc123');
    });

    it('throws on network error', async () => {
        vi.spyOn(globalThis, 'fetch').mockRejectedValue(new TypeError('Failed to fetch'));

        await expect(requestWrapper('query { ok }')).rejects.toThrow('Network error');
    });

    it('throws on non-ok HTTP response', async () => {
        vi.spyOn(globalThis, 'fetch').mockResolvedValue({
            ok: false,
            status: 500,
        } as Response);

        await expect(requestWrapper('query { ok }')).rejects.toThrow('Server error (500)');
    });

    it('throws on GraphQL errors', async () => {
        vi.spyOn(globalThis, 'fetch').mockResolvedValue({
            ok: true,
            json: async () => ({ errors: [{ message: 'Invalid credentials' }] }),
        } as Response);

        await expect(requestWrapper('mutation { login }')).rejects.toThrow('Invalid credentials');
    });

    it('throws when response has no data field', async () => {
        vi.spyOn(globalThis, 'fetch').mockResolvedValue({
            ok: true,
            json: async () => ({}),
        } as Response);

        await expect(requestWrapper('query { ok }')).rejects.toThrow('missing "data" field');
    });

    it('throws on invalid JSON response', async () => {
        vi.spyOn(globalThis, 'fetch').mockResolvedValue({
            ok: true,
            json: async () => { throw new SyntaxError('Unexpected token'); },
        } as Response);

        await expect(requestWrapper('query { ok }')).rejects.toThrow('Invalid server response');
    });
});
