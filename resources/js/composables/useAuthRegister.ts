import { ref } from 'vue';
import { requestWrapper } from '@/lib/graphql';

type RegisterInput = {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
};

type RegisterResultUser = {
    id: string;
    name: string;
    email: string;
};

export function useAuthRegister() {
    const submitting = ref(false);

    const register = async (input: RegisterInput): Promise<RegisterResultUser> => {
        submitting.value = true;

        try {
            const data = await requestWrapper<{
                register: { user: RegisterResultUser };
            }>(
                `
                    mutation Register($input: RegisterInput!) {
                        register(input: $input) {
                            user {
                                id
                                name
                                email
                            }
                        }
                    }
                `,
                { input },
            );

            const user: RegisterResultUser | undefined = data.register?.user;

            if (! user) {
                throw new Error('Invalid registration response.');
            }

            return user;
        } finally {
            submitting.value = false;
        }
    };

    return {
        submitting,
        register,
    };
}

