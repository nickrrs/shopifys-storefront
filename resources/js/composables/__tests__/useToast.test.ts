import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { useToast } from '../useToast';

describe('useToast', () => {
    beforeEach(() => {
        vi.useFakeTimers();

        const { toasts } = useToast();
        toasts.value = [];
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    it('returns toasts ref, showError and showSuccess functions', () => {
        const { toasts, showError, showSuccess } = useToast();

        expect(Array.isArray(toasts.value)).toBe(true);
        expect(typeof showError).toBe('function');
        expect(typeof showSuccess).toBe('function');
    });

    it('showError adds an error toast', () => {
        const { toasts, showError } = useToast();

        showError('Something went wrong');

        expect(toasts.value).toHaveLength(1);
        expect(toasts.value[0].type).toBe('error');
        expect(toasts.value[0].message).toBe('Something went wrong');
    });

    it('showSuccess adds a success toast', () => {
        const { toasts, showSuccess } = useToast();

        showSuccess('Profile updated');

        expect(toasts.value).toHaveLength(1);
        expect(toasts.value[0].type).toBe('success');
        expect(toasts.value[0].message).toBe('Profile updated');
    });

    it('auto-removes toast after 4 seconds', () => {
        const { toasts, showSuccess } = useToast();

        showSuccess('Temporary toast');

        expect(toasts.value).toHaveLength(1);

        vi.advanceTimersByTime(3999);
        expect(toasts.value).toHaveLength(1);

        vi.advanceTimersByTime(1);
        expect(toasts.value).toHaveLength(0);
    });

    it('supports multiple toasts simultaneously', () => {
        const { toasts, showError, showSuccess } = useToast();

        showError('Error 1');
        showSuccess('Success 1');
        showError('Error 2');

        expect(toasts.value).toHaveLength(3);
        expect(toasts.value[0].type).toBe('error');
        expect(toasts.value[1].type).toBe('success');
        expect(toasts.value[2].type).toBe('error');
    });

    it('each toast has a unique id', () => {
        const { toasts, showError, showSuccess } = useToast();

        showError('A');
        showSuccess('B');

        const ids = toasts.value.map((t) => t.id);

        expect(new Set(ids).size).toBe(ids.length);
    });

    it('shares state across multiple useToast calls', () => {
        const toast1 = useToast();
        const toast2 = useToast();

        toast1.showError('Shared error');

        expect(toast2.toasts.value).toHaveLength(1);
        expect(toast2.toasts.value[0].message).toBe('Shared error');
    });
});
