import { ref } from 'vue';
import { requestWrapper } from '@/lib/graphql';

type UpdatePasswordInput = {
    current_password: string;
    password: string;
    password_confirmation: string;
};

export function usePasswordUpdate() {
    const submitting = ref(false);

    const updatePassword = async (input: UpdatePasswordInput): Promise<boolean> => {
        submitting.value = true;

        try {
            const data = await requestWrapper<{ updatePassword: boolean }>(
                `
                    mutation UpdatePassword($input: UpdatePasswordInput!) {
                        updatePassword(input: $input)
                    }
                `,
                { input },
            );

            return data.updatePassword;
        } finally {
            submitting.value = false;
        }
    };

    return { submitting, updatePassword };
}
