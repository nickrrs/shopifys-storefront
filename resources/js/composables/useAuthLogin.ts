import { ref } from 'vue';
import { requestWrapper } from '@/lib/graphql';

type LoginInput = {
    email: string;
    password: string;
};

type LoginResultUser = {
    id: string;
    name: string;
    email: string;
};

export function useAuthLogin() {
    const submitting = ref(false);

    const login = async (input: LoginInput): Promise<LoginResultUser> => {
        submitting.value = true;

        try {
            const data = await requestWrapper<{
                login: { user: LoginResultUser };
            }>(
                `
                    mutation Login($input: LoginInput!) {
                        login(input: $input) {
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

            const user: LoginResultUser | undefined = data.login?.user;

            if (! user) {
                throw new Error('Invalid authentication response.');
            }

            return user;
        } finally {
            submitting.value = false;
        }
    };

    return {
        submitting,
        login,
    };
}
