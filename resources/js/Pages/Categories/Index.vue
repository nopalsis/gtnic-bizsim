<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import SidebarNav from '@/Components/SidebarNav.vue';
import CategoryHeader from './Partials/CategoryHeader.vue';
import CategoryKpiCards from './Partials/CategoryKpiCards.vue';
import CategoryTable from './Partials/CategoryTable.vue';
import AddEditCategoryModal from './Partials/AddEditCategoryModal.vue';
import DeleteCategoryModal from './Partials/DeleteCategoryModal.vue';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    counts: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();

// Modals State
const showAddEditModal = ref(false);
const editingCategory = ref(null);

const showDeleteModal = ref(false);
const deletingCategory = ref(null);

const openAddModal = () => {
    editingCategory.value = null;
    showAddEditModal.value = true;
};

const openEditModal = (category) => {
    editingCategory.value = category;
    showAddEditModal.value = true;
};

const openDeleteModal = (category) => {
    deletingCategory.value = category;
    showDeleteModal.value = true;
};
</script>

<template>
    <Head title="Kategori Produk & Bahan · OptimaBiz" />

    <div class="min-h-screen w-full max-w-full overflow-x-hidden bg-[#eaecdf] p-2.5 md:p-5 pb-24 md:pb-5 flex flex-col md:flex-row gap-5 font-sans">
        <!-- Sidebar Navigation Rail -->
        <SidebarNav active="categories" />

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col gap-5 min-w-0 max-w-full">
            <!-- Flash / Error Alert -->
            <div
                v-if="page.props.errors && page.props.errors.delete"
                class="p-4 bg-amber-50 rounded-2xl border border-amber-300 flex items-start gap-3 shadow-xs"
            >
                <span class="material-symbols-outlined text-amber-600 text-[20px] shrink-0 mt-0.5">warning</span>
                <div class="flex-1">
                    <p class="text-xs font-bold text-amber-900 mb-0.5">Perhatian</p>
                    <p class="text-xs text-amber-800">{{ page.props.errors.delete }}</p>
                </div>
            </div>

            <!-- Header Row -->
            <CategoryHeader
                @open-add-modal="openAddModal"
            />

            <!-- 3 Top Summary KPI Cards -->
            <CategoryKpiCards
                :counts="props.counts"
            />

            <!-- Main Categories Table Section -->
            <CategoryTable
                :categories="props.categories"
                :counts="props.counts"
                @open-edit="openEditModal"
                @open-delete="openDeleteModal"
            />
        </main>
    </div>

    <!-- Modals -->
    <AddEditCategoryModal
        :show="showAddEditModal"
        :category="editingCategory"
        @close="showAddEditModal = false"
    />

    <DeleteCategoryModal
        :show="showDeleteModal"
        :category="deletingCategory"
        @close="showDeleteModal = false"
    />
</template>

