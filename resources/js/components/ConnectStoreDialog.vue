<script setup lang="ts">
import { Store as StoreIcon, Loader2, Eye, EyeOff } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useConnectStore } from '@/composables/useConnectStore';
import { useToast } from '@/composables/useToast';

const open = defineModel<boolean>('open', { default: false });

const emit = defineEmits<{
    connected: [];
}>();

const name = ref('');
const shopifyDomain = ref('');
const accessToken = ref('');
const showToken = ref(false);

const { submitting, connectStore } = useConnectStore();
const { showError, showSuccess } = useToast();

const resetForm = () => {
    name.value = '';
    shopifyDomain.value = '';
    accessToken.value = '';
    showToken.value = false;
};

watch(open, (isOpen) => {
    if (!isOpen) {
        resetForm();
    }
});

const handleSubmit = async () => {
    if (!name.value.trim() || !shopifyDomain.value.trim() || !accessToken.value.trim()) {
        showError('all fields are required.');
        return;
    }

    try {
        await connectStore({
            name: name.value.trim(),
            shopifyDomain: shopifyDomain.value.trim(),
            accessToken: accessToken.value.trim(),
        });

        showSuccess('store connected successfully!');
        open.value = false;
        emit('connected');
    } catch (error) {
        const message =
            error instanceof Error
                ? error.message
                : 'unexpected error when connecting store.';

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
                        <StoreIcon class="h-4 w-4 text-white" />
                    </div>
                    <DialogTitle class="text-lg font-semibold text-gray-900">
                        connect store
                    </DialogTitle>
                </div>
                <DialogDescription class="text-sm text-gray-500 mt-1">
                    enter your Shopify store details to connect it.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4 mt-2" @submit.prevent="handleSubmit">
                <div class="grid gap-2">
                    <Label for="store-name" class="text-sm font-medium text-gray-700">
                        name
                    </Label>
                    <Input
                        id="store-name"
                        v-model="name"
                        type="text"
                        placeholder="my store"
                        required
                        class="focus:border-[#008060] focus:ring-[#008060]"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="shopify-domain" class="text-sm font-medium text-gray-700">
                        shopify domain
                    </Label>
                    <Input
                        id="shopify-domain"
                        v-model="shopifyDomain"
                        type="text"
                        placeholder="my-store.myshopify.com"
                        required
                        class="focus:border-[#008060] focus:ring-[#008060]"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="access-token" class="text-sm font-medium text-gray-700">
                        access token
                    </Label>
                    <div class="relative">
                        <Input
                            id="access-token"
                            v-model="accessToken"
                            :type="showToken ? 'text' : 'password'"
                            placeholder="shpat_xxxxxxxxxxxxxxxx"
                            required
                            class="focus:border-[#008060] focus:ring-[#008060] pr-10"
                        />
                        <button
                            type="button"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none"
                            @click="showToken = !showToken"
                            tabindex="-1"
                        >
                            <EyeOff v-if="showToken" class="h-4 w-4" />
                            <Eye v-else class="h-4 w-4" />
                        </button>
                    </div>
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
                        <template v-else>connect</template>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
