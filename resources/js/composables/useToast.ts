import { ref } from 'vue';

type ToastType = 'error' | 'success';

export type Toast = {
    id: number;
    type: ToastType;
    message: string;
};

const toasts = ref<Toast[]>([]);
let idCounter = 1;

function pushToast(type: ToastType, message: string, duration = 4000) {
    const id = idCounter++;

    toasts.value.push({ id, type, message });

    if (duration > 0) {
        setTimeout(() => {
            toasts.value = toasts.value.filter((toast) => toast.id !== id);
        }, duration);
    }
}

export function useToast() {
    const showError = (message: string) => pushToast('error', message);
    const showSuccess = (message: string) => pushToast('success', message);

    return {
        toasts,
        showError,
        showSuccess,
    };
}

