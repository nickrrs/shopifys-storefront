<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { usePasswordUpdate } from '@/composables/usePasswordUpdate';
import { useToast } from '@/composables/useToast';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/user-password';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'password settings',
        href: edit().url,
    },
];

const currentPassword = ref('');
const password = ref('');
const passwordConfirmation = ref('');

const { submitting, updatePassword } = usePasswordUpdate();
const { toasts, showError, showSuccess } = useToast();

const resetForm = () => {
    currentPassword.value = '';
    password.value = '';
    passwordConfirmation.value = '';
};

const submit = async () => {
    try {
        await updatePassword({
            current_password: currentPassword.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });

        showSuccess('Password updated successfully.');
        resetForm();
    } catch (error) {
        const message =
            error instanceof Error
                ? error.message
                : 'Unexpected error when updating password.';

        showError(message);
        password.value = '';
        passwordConfirmation.value = '';
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="password settings" />

        <ToastContainer :toasts="toasts" />

        <h1 class="sr-only">password settings</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="update password"
                    description="ensure your account is using a long, random password to stay secure"
                />

                <form class="space-y-6" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="current_password">current password</Label>
                        <Input
                            id="current_password"
                            v-model="currentPassword"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="current-password"
                            placeholder="current password"
                            required
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">new password</Label>
                        <Input
                            id="password"
                            v-model="password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="new password"
                            required
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">confirm password</Label>
                        <Input
                            id="password_confirmation"
                            v-model="passwordConfirmation"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="confirm password"
                            required
                        />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            type="submit"
                            :disabled="submitting"
                            data-test="update-password-button"
                        >
                            save password
                        </Button>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
