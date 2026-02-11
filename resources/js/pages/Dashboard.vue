<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Plus, Store as StoreIcon, Loader2 } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import ConnectStoreDialog from '@/components/ConnectStoreDialog.vue';
import EditStoreDialog from '@/components/EditStoreDialog.vue';
import StoreCard from '@/components/StoreCard.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { useStores, type StoreItem } from '@/composables/useStores';
import { useSyncStore } from '@/composables/useSyncStore';
import { useToast } from '@/composables/useToast';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'stores',
        href: dashboard().url,
    },
];

const showConnectDialog = ref(false);
const showEditDialog = ref(false);
const editingStore = ref<StoreItem | null>(null);
const expandedStores = ref<Set<string>>(new Set());
const loadingMore = ref<Set<string>>(new Set());

const { stores, loading, fetchStores, loadMoreProducts, setStoreSyncing } = useStores();
const { syncStoreProducts } = useSyncStore();
const { toasts, showError, showSuccess } = useToast();

const toggleExpand = (storeId: string) => {
    if (expandedStores.value.has(storeId)) {
        expandedStores.value.delete(storeId);
    } else {
        expandedStores.value.add(storeId);
    }
};

const openEditDialog = (store: StoreItem) => {
    editingStore.value = store;
    showEditDialog.value = true;
};

const handleSync = async (store: StoreItem) => {
    if (store.syncing) return;

    try {
        await syncStoreProducts(store.id);
        setStoreSyncing(store.id, true);
        showSuccess(`syncing products for "${store.name}"...`);
        pollSyncStatus();
    } catch (error) {
        const message =
            error instanceof Error
                ? error.message
                : 'unexpected error when syncing products.';

        showError(message);
    }
};

let pollInterval: ReturnType<typeof setInterval> | null = null;

const pollSyncStatus = () => {
    if (pollInterval) return;

    pollInterval = setInterval(async () => {
        await fetchStores();

        const anySyncing = stores.value.some((s) => s.syncing);

        if (!anySyncing && pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }, 3000);
};

const handleLoadMore = async (store: StoreItem) => {
    const cursor = store.products?.pageInfo?.endCursor;

    if (!cursor || loadingMore.value.has(store.id)) return;

    loadingMore.value.add(store.id);

    try {
        await loadMoreProducts(store.id, cursor);
    } catch (error) {
        const message =
            error instanceof Error
                ? error.message
                : 'unexpected error loading more products.';

        showError(message);
    } finally {
        loadingMore.value.delete(store.id);
    }
};

const handleStoreConnected = () => fetchStores();
const handleStoreUpdated = () => fetchStores();

onMounted(() => {
    fetchStores();
});
</script>

<template>
    <Head title="stores" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ToastContainer :toasts="toasts" />

        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4 sm:p-6 bg-[#f1f2f3]">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-lg sm:text-xl font-semibold text-gray-900">
                        stores overview
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        visualize your stores and products in one place.
                    </p>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg bg-[#008060] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#006e52] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#008060] transition-all duration-200 cursor-pointer w-full sm:w-auto"
                    @click="showConnectDialog = true"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    connect store
                </button>
            </div>

            <div
                v-if="loading"
                class="flex flex-1 items-center justify-center rounded-xl border border-gray-200 bg-white mt-4 py-16"
            >
                <Loader2 class="h-6 w-6 animate-spin text-[#008060]" />
            </div>

            <div
                v-else-if="stores.length === 0"
                class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-white mt-4 py-16"
            >
                <div class="inline-flex items-center justify-center p-3 bg-gray-100 rounded-full mb-4">
                    <StoreIcon class="h-6 w-6 text-gray-400" />
                </div>
                <h3 class="text-sm font-medium text-gray-900">no stores connected</h3>
                <p class="mt-1 text-sm text-gray-500">
                    connect your first Shopify store to get started.
                </p>
                <button
                    type="button"
                    class="mt-4 inline-flex items-center rounded-lg bg-[#008060] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#006e52] transition-all duration-200 cursor-pointer"
                    @click="showConnectDialog = true"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    connect store
                </button>
            </div>

            <div v-else class="flex flex-col gap-4 mt-4">
                <StoreCard
                    v-for="store in stores"
                    :key="store.id"
                    :store="store"
                    :expanded="expandedStores.has(store.id)"
                    :loading-more="loadingMore.has(store.id)"
                    @toggle-expand="toggleExpand(store.id)"
                    @edit="openEditDialog(store)"
                    @sync="handleSync(store)"
                    @load-more="handleLoadMore(store)"
                />
            </div>
        </div>

        <ConnectStoreDialog
            v-model:open="showConnectDialog"
            @connected="handleStoreConnected"
        />

        <EditStoreDialog
            v-model:open="showEditDialog"
            :store="editingStore"
            @updated="handleStoreUpdated"
        />
    </AppLayout>
</template>
