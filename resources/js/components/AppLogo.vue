<script setup lang="ts">
import { Store } from 'lucide-vue-next';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-[#008060] text-sidebar-primary-foreground"
    >
        <Store class="h-4 w-4 text-white" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold"
            >shopify's storefront</span
        >
    </div>
</template>
