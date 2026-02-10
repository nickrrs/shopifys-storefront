import { ref } from 'vue';
import { requestWrapper } from '@/lib/graphql';

export function useDeleteAccount() {
    const submitting = ref(false);

    const deleteAccount = async (password: string): Promise<boolean> => {
        submitting.value = true;

        try {
            const data = await requestWrapper<{ deleteAccount: boolean }>(
                `
                    mutation DeleteAccount($input: DeleteAccountInput!) {
                        deleteAccount(input: $input)
                    }
                `,
                { input: { password } },
            );

            return data.deleteAccount;
        } finally {
            submitting.value = false;
        }
    };

    return { submitting, deleteAccount };
}
