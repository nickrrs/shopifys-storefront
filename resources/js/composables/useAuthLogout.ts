import { ref } from 'vue';
import { requestWrapper } from '@/lib/graphql';

export function useAuthLogout() {
    const submitting = ref(false);

    const logout = async () => {
        if (submitting.value) return;

        submitting.value = true;

        try {
            await requestWrapper<{ logout: boolean }>(
                `
                    mutation Logout {
                        logout
                    }
                `,
            );

            window.location.href = '/';
        } finally {
            submitting.value = false;
        }
    };

    return {
        submitting,
        logout,
    };
}

