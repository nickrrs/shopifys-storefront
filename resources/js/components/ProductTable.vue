<script setup lang="ts">
import { Loader2, Package, RefreshCw } from 'lucide-vue-next';
import { computed } from 'vue';
import { useInfiniteScroll } from '@/composables/useInfiniteScroll';
import type { StoreItem } from '@/composables/useStores';

const props = defineProps<{
    store: StoreItem;
    loadingMore: boolean;
}>();

const emit = defineEmits<{
    sync: [event: Event];
    loadMore: [];
}>();

const { observe } = useInfiniteScroll({
    onLoadMore: () => {
        if (hasMore.value && !props.loadingMore) {
            emit('loadMore');
        }
    },
});

const products = computed(() => {
    return props.store.products?.edges?.map((edge) => edge.node) ?? [];
});

const hasMore = computed(() => {
    return props.store.products?.pageInfo?.hasNextPage ?? false;
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const statusColor = (status: string) => {
    switch (status.toLowerCase()) {
        case 'active':
            return 'bg-green-50 text-green-700';
        case 'draft':
            return 'bg-yellow-50 text-yellow-700';
        case 'archived':
            return 'bg-gray-100 text-gray-600';
        default:
            return 'bg-blue-50 text-blue-700';
    }
};
</script>

<template>
    <div class="border-t border-gray-100">
        <div
            v-if="products.length === 0 && !store.syncing"
            class="flex flex-col items-center justify-center py-10 text-center"
        >
            <Package class="h-8 w-8 text-gray-300 mb-3" />
            <p class="text-sm font-medium text-gray-900">no products found</p>
            <p class="mt-1 text-xs text-gray-500">
                click "sync now" to fetch products from Shopify.
            </p>
            <button
                type="button"
                class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-[#008060] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#006e52] transition-all cursor-pointer"
                @click="emit('sync', $event)"
            >
                <RefreshCw class="h-3.5 w-3.5" />
                sync now
            </button>
        </div>

        <div
            v-else-if="products.length === 0 && store.syncing"
            class="flex items-center justify-center py-10"
        >
            <Loader2 class="h-5 w-5 animate-spin text-[#008060] mr-2" />
            <span class="text-sm text-gray-500">syncing products from Shopify...</span>
        </div>

        <div v-else>
            <div
                v-if="store.syncing"
                class="flex items-center gap-2 px-5 py-2 bg-amber-50 border-b border-amber-100 text-xs text-amber-700"
            >
                <Loader2 class="h-3.5 w-3.5 animate-spin" />
                syncing products... new products will appear shortly.
            </div>

            <div data-scroll-container class="max-h-96 overflow-y-auto overflow-x-auto">
                <table class="w-full min-w-[500px] text-sm">
                    <thead class="sticky top-0 bg-white z-10">
                        <tr class="border-b border-gray-100 text-left">
                            <th class="px-3 sm:px-5 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                title
                            </th>
                            <th class="px-3 sm:px-5 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                price
                            </th>
                            <th class="px-3 sm:px-5 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                inventory
                            </th>
                            <th class="px-3 sm:px-5 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                status
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="product in products"
                            :key="product.id"
                            class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors"
                        >
                            <td class="px-3 sm:px-5 py-3 font-medium text-gray-900 max-w-[180px] sm:max-w-xs truncate">
                                {{ product.title }}
                            </td>
                            <td class="px-3 sm:px-5 py-3 text-gray-600 whitespace-nowrap">
                                {{ formatPrice(product.price) }}
                            </td>
                            <td class="px-3 sm:px-5 py-3 text-gray-600 hidden sm:table-cell">
                                {{ product.inventoryQuantity ?? '—' }}
                            </td>
                            <td class="px-3 sm:px-5 py-3">
                                <span
                                    :class="[
                                        'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                        statusColor(product.status),
                                    ]"
                                >
                                    {{ product.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div
                    v-if="hasMore"
                    :ref="(el) => observe(el as HTMLElement, store.id)"
                    class="flex items-center justify-center py-3"
                >
                    <Loader2
                        v-if="loadingMore"
                        class="h-4 w-4 animate-spin text-[#008060]"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
