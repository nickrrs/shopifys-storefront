<script setup lang="ts">
import type { Toast } from '@/composables/useToast';

defineProps<{
    toasts: Toast[];
}>();
</script>

<template>
    <div
        v-if="toasts.length"
        class="pointer-events-none fixed bottom-4 right-4 z-100 flex flex-col items-end gap-2"
    >
        <div
            v-for="toast in toasts"
            :key="toast.id"
            :class="[
                'pointer-events-auto w-full max-w-sm rounded-md border px-3 py-2 text-sm shadow-lg',
                toast.type === 'error'
                    ? 'border-red-200 bg-red-50 text-red-700'
                    : 'border-green-200 bg-green-50 text-green-700',
            ]"
        >
            {{ toast.message }}
        </div>
    </div>
</template>
