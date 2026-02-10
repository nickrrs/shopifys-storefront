<script setup lang="ts">
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useDeleteAccount } from '@/composables/useDeleteAccount';
import { useToast } from '@/composables/useToast';

const password = ref('');
const dialogOpen = ref(false);

const { submitting, deleteAccount } = useDeleteAccount();
const { showError } = useToast();

const handleDelete = async () => {
    try {
        await deleteAccount(password.value);

        window.location.href = '/';
    } catch (error) {
        const message =
            error instanceof Error
                ? error.message
                : 'Unexpected error when deleting account.';

        showError(message);
        password.value = '';
    }
};

const handleCancel = () => {
    password.value = '';
    dialogOpen.value = false;
};
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            title="delete account"
            description="Delete your account and all of its resources"
        />
        <div
            class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10"
        >
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">warning</p>
                <p class="text-sm">
                    please proceed with caution, this cannot be undone.
                </p>
            </div>
            <Dialog v-model:open="dialogOpen">
                <DialogTrigger as-child>
                    <Button variant="destructive" data-test="delete-user-button">
                        delete account
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <form class="space-y-6" @submit.prevent="handleDelete">
                        <DialogHeader class="space-y-3">
                            <DialogTitle>
                                are you sure you want to delete your account?
                            </DialogTitle>
                            <DialogDescription>
                                once your account is deleted, all of its resources and
                                data will also be permanently deleted. please enter your
                                password to confirm you would like to permanently delete
                                your account.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="delete-password" class="sr-only">
                                password
                            </Label>
                            <Input
                                id="delete-password"
                                v-model="password"
                                type="password"
                                placeholder="password"
                                required
                            />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    @click="handleCancel"
                                >
                                    cancel
                                </Button>
                            </DialogClose>

                            <Button
                                type="submit"
                                variant="destructive"
                                :disabled="submitting"
                                data-test="confirm-delete-user-button"
                            >
                                delete account
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
