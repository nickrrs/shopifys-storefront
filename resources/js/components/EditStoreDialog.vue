<script setup lang="ts">
import { ref, watch } from 'vue';
import { Pencil, Loader2 } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useUpdateStore } from '@/composables/useUpdateStore';
import { useToast } from '@/composables/useToast';
import type { StoreItem } from '@/composables/useStores';

type Props = {
    store: StoreItem | null;
};

const props = defineProps<Props>();

const open = defineModel<boolean>('open', { default: false });

const emit = defineEmits<{
    updated: [];
}>();

const name = ref('');

const { submitting, updateStore } = useUpdateStore();
const { showError, showSuccess } = useToast();

watch(open, (isOpen) => {
    if (isOpen && props.store) {
        name.value = props.store.name;
    }
});

const handleSubmit = async () => {
    if (!props.store) return;

    if (!name.value.trim()) {
        showError('name is required.');
        return;
    }

    try {
        await updateStore(props.store.id, {
            name: name.value.trim(),
        });

        showSuccess('store updated successfully!');
        open.value = false;
        emit('updated');
    } catch (error) {
        const message =
            error instanceof Error
                ? error.message
                : 'unexpected error when updating store.';

        showError(message);
    }
};
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <div class="flex items-center gap-2">
                    <div
                        class="inline-flex items-center justify-center p-2 bg-[#008060] rounded-lg"
                    >
                        <Pencil class="h-4 w-4 text-white" />
                    </div>
                    <DialogTitle class="text-lg font-semibold text-gray-900">
                        edit store
                    </DialogTitle>
                </div>
                <DialogDescription class="text-sm text-gray-500 mt-1">
                    update your store details.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4 mt-2" @submit.prevent="handleSubmit">
                <div class="grid gap-2">
                    <Label for="edit-store-name" class="text-sm font-medium text-gray-700">
                        name
                    </Label>
                    <Input
                        id="edit-store-name"
                        v-model="name"
                        type="text"
                        placeholder="my store"
                        required
                        class="focus:border-[#008060] focus:ring-[#008060]"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="edit-shopify-domain" class="text-sm font-medium text-gray-700">
                        shopify domain
                    </Label>
                    <Input
                        id="edit-shopify-domain"
                        :model-value="store?.shopifyDomain ?? ''"
                        type="text"
                        disabled
                        class="bg-gray-50 text-gray-500 cursor-not-allowed"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="edit-access-token" class="text-sm font-medium text-gray-700">
                        access token
                    </Label>
                    <Input
                        id="edit-access-token"
                        model-value="••••••••••••••••••••"
                        type="password"
                        disabled
                        class="bg-gray-50 text-gray-500 cursor-not-allowed"
                    />
                </div>

                <DialogFooter class="pt-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="open = false"
                        :disabled="submitting"
                    >
                        cancel
                    </Button>
                    <Button
                        type="submit"
                        :disabled="submitting"
                        class="bg-[#008060] hover:bg-[#006e52] text-white"
                    >
                        <Loader2 v-if="submitting" class="mr-2 h-4 w-4 animate-spin" />
                        <template v-else>save</template>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
