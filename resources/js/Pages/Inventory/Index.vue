<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import SidebarNav from '@/Components/SidebarNav.vue';
import InventoryHeader from './Partials/InventoryHeader.vue';
import InventoryKpiCards from './Partials/InventoryKpiCards.vue';
import InventoryTable from './Partials/InventoryTable.vue';
import WasteLogAuditSection from './Partials/WasteLogAuditSection.vue';
import AddEditProductModal from './Partials/AddEditProductModal.vue';
import RecordWasteModal from './Partials/RecordWasteModal.vue';
import StockMutationModal from './Partials/StockMutationModal.vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    recentMovements: {
        type: Array,
        default: () => [],
    },
    recentWasteLogs: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    advisory: {
        type: Object,
        default: () => ({}),
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

// Modal States
const showAddEditModal = ref(false);
const editingProduct = ref(null);

const showWasteModal = ref(false);

const showMutationModal = ref(false);
const mutatingProduct = ref(null);

// Modal Handlers
const openAddModal = () => {
    editingProduct.value = null;
    showAddEditModal.value = true;
};

const openEditModal = (product) => {
    editingProduct.value = product;
    showAddEditModal.value = true;
};

const openWasteModal = () => {
    showWasteModal.value = true;
};

const openMutationModal = (product) => {
    mutatingProduct.value = product;
    showMutationModal.value = true;
};

// Delete Product Confirmation
const confirmDeleteProduct = (product) => {
    if (confirm(`Apakah Anda yakin ingin menghapus atau menonaktifkan "${product.name}"?`)) {
        router.delete(`/inventory/product/${product.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Inventory & Materials · OptimaBiz" />

    <div class="min-h-screen w-full bg-[#eaecdf] p-2 md:p-5 flex flex-col md:flex-row gap-5 font-sans">
        <!-- Sidebar Navigation Rail -->
        <SidebarNav active="inventory" />

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col gap-5 min-w-0">
            <!-- Header Row -->
            <InventoryHeader
                @open-add-modal="openAddModal"
                @open-waste-modal="openWasteModal"
            />

            <!-- 3 Top Summary KPI Cards -->
            <InventoryKpiCards
                :stats="props.stats"
            />

            <!-- Main Inventory Table Section -->
            <InventoryTable
                :products="props.products"
                @open-mutation="openMutationModal"
                @open-edit="openEditModal"
                @delete-product="confirmDeleteProduct"
            />

            <!-- Interactive Waste Log Quick Preview Section (Bottom Asymmetric Panel) -->
            <WasteLogAuditSection
                :recent-waste-logs="props.recentWasteLogs"
                :advisory="props.advisory"
                @open-waste-modal="openWasteModal"
            />
        </main>
    </div>

    <!-- Modals -->
    <AddEditProductModal
        :show="showAddEditModal"
        :product="editingProduct"
        :categories="props.categories"
        @close="showAddEditModal = false"
    />

    <RecordWasteModal
        :show="showWasteModal"
        :products="props.products"
        @close="showWasteModal = false"
    />

    <StockMutationModal
        :show="showMutationModal"
        :product="mutatingProduct"
        @close="showMutationModal = false"
    />
</template>
