import { ref } from 'vue';
import { requestWrapper } from '@/lib/graphql';

type UpdateProfileInput = {
    name: string;
    email: string;
};

type ProfileUser = {
    id: string;
    name: string;
    email: string;
};

export function useProfileUpdate() {
    const submitting = ref(false);

    const updateProfile = async (input: UpdateProfileInput): Promise<ProfileUser> => {
        submitting.value = true;

        try {
            const data = await requestWrapper<{ updateProfile: ProfileUser }>(
                `
                    mutation UpdateProfile($input: UpdateProfileInput!) {
                        updateProfile(input: $input) {
                            id
                            name
                            email
                        }
                    }
                `,
                { input },
            );

            return data.updateProfile;
        } finally {
            submitting.value = false;
        }
    };

    return { submitting, updateProfile };
}
