<script setup lang="ts">
import { computed } from 'vue';
import {
    Store as StoreIcon,
    Globe,
    Calendar,
    Loader2,
    Pencil,
    RefreshCw,
    ChevronDown,
    ChevronUp,
    Package,
} from 'lucide-vue-next';
import ProductTable from '@/components/ProductTable.vue';
import type { StoreItem } from '@/composables/useStores';

const props = defineProps<{
    store: StoreItem;
    expanded: boolean;
    loadingMore: boolean;
}>();

const emit = defineEmits<{
    toggleExpand: [];
    edit: [event: Event];
    sync: [event: Event];
    loadMore: [];
}>();

const productCount = computed(() => props.store.productsCount ?? 0);

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <div
        class="rounded-xl border border-gray-200 bg-white shadow-sm transition-all duration-200"
        :class="{ 'border-[#008060]/30': expanded }"
    >
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 sm:p-5 cursor-pointer hover:bg-gray-50/50 transition-colors rounded-t-xl"
            @click="emit('toggleExpand')"
        >
            <div class="flex items-start gap-3 flex-1 min-w-0">
                <div class="inline-flex items-center justify-center p-2 sm:p-2.5 bg-[#008060]/10 rounded-lg shrink-0">
                    <StoreIcon class="h-4 w-4 sm:h-5 sm:w-5 text-[#008060]" />
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-semibold text-gray-900 truncate">
                        {{ store.name }}
                    </h3>
                    <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                        <div class="flex items-center gap-1.5 text-xs text-gray-500">
                            <Globe class="h-3.5 w-3.5 shrink-0" />
                            <span class="truncate max-w-[140px] sm:max-w-none">{{ store.shopifyDomain }}</span>
                        </div>
                        <div class="hidden sm:flex items-center gap-1.5 text-xs text-gray-400">
                            <Calendar class="h-3.5 w-3.5 shrink-0" />
                            <span>{{ formatDate(store.connectedAt) }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-gray-400">
                            <Package class="h-3.5 w-3.5 shrink-0" />
                            <span>{{ productCount }} product{{ productCount !== 1 ? 's' : '' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 ml-auto sm:ml-4 shrink-0">
                <span
                    v-if="store.syncing"
                    class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700"
                >
                    <Loader2 class="h-3 w-3 animate-spin" />
                    <span class="hidden sm:inline">syncing</span>
                </span>
                <span
                    v-else
                    class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700"
                >
                    <span class="hidden sm:inline">connected</span>
                    <span class="sm:hidden">ok</span>
                </span>

                <button
                    type="button"
                    class="p-1.5 cursor-pointer rounded-md text-gray-400 hover:text-[#008060] hover:bg-[#008060]/10 transition-colors"
                    :class="{ 'opacity-50 cursor-not-allowed': store.syncing }"
                    :disabled="store.syncing"
                    title="sync products"
                    @click.stop="emit('sync', $event)"
                >
                    <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': store.syncing }" />
                </button>

                <button
                    type="button"
                    class="p-1.5 cursor-pointer rounded-md text-gray-400 hover:text-[#008060] hover:bg-[#008060]/10 transition-colors"
                    title="edit store"
                    @click.stop="emit('edit', $event)"
                >
                    <Pencil class="h-4 w-4" />
                </button>

                <ChevronUp v-if="expanded" class="h-4 w-4 text-gray-400" />
                <ChevronDown v-else class="h-4 w-4 text-gray-400" />
            </div>
        </div>

        <ProductTable
            v-if="expanded"
            :store="store"
            :loading-more="loadingMore"
            @sync="emit('sync', $event)"
            @load-more="emit('loadMore')"
        />
    </div>
</template>
