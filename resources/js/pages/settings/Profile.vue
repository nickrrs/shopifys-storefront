<script setup lang="ts">
import { ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/profile';
import ToastContainer from '@/components/ToastContainer.vue';
import { useProfileUpdate } from '@/composables/useProfileUpdate';
import { useToast } from '@/composables/useToast';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'profile settings',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;

const name = ref(user.name);
const email = ref(user.email);

const { submitting, updateProfile } = useProfileUpdate();
const { toasts, showError, showSuccess } = useToast();

const submit = async () => {
    try {
        const updatedUser = await updateProfile({
            name: name.value,
            email: email.value,
        });

        name.value = updatedUser.name;
        email.value = updatedUser.email;

        showSuccess('Profile updated successfully.');
    } catch (error) {
        const message =
            error instanceof Error
                ? error.message
                : 'Unexpected error when updating profile.';

        showError(message);
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="profile settings" />

        <ToastContainer :toasts="toasts" />

        <h1 class="sr-only">profile settings</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="profile information"
                    description="update your name and email address"
                />

                <form class="space-y-6" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="name">name</Label>
                        <Input
                            id="name"
                            v-model="name"
                            class="mt-1 block w-full"
                            type="text"
                            required
                            autocomplete="name"
                            placeholder="full name"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">email address</Label>
                        <Input
                            id="email"
                            v-model="email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                            autocomplete="username"
                            placeholder="email address"
                        />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            type="submit"
                            :disabled="submitting"
                            data-test="update-profile-button"
                        >
                            save
                        </Button>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
