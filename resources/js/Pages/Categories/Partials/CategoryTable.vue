<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    counts: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['openEdit', 'openDelete']);

// State Filter & Search
const searchQuery = ref('');
const activeTab = ref('all'); // 'all' | 'finished' | 'raw'
const statusFilter = ref('all'); // 'all' | 'active' | 'inactive'

const filteredCategories = computed(() => {
    let result = [...props.categories];

    // Filter Tab Jenis
    if (activeTab.value === 'finished') {
        result = result.filter((c) => c.type === 'finished');
    } else if (activeTab.value === 'raw') {
        result = result.filter((c) => c.type === 'raw');
    }

    // Filter Status
    if (statusFilter.value === 'active') {
        result = result.filter((c) => c.is_active);
    } else if (statusFilter.value === 'inactive') {
        result = result.filter((c) => !c.is_active);
    }

    // Filter Pencarian Teks
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase().trim();
        result = result.filter(
            (c) =>
                c.name?.toLowerCase().includes(q) ||
                c.prefix_sku?.toLowerCase().includes(q) ||
                c.description?.toLowerCase().includes(q)
        );
    }

    return result;
});

const toggleCategoryStatus = (category) => {
    router.patch(`/categories/${category.id}/toggle-status`, {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <section class="bg-white rounded-2xl md:rounded-[24px] p-4 md:p-6 shadow-xs border border-[#dce0cd] overflow-hidden flex flex-col">
        <!-- Toolbar Header Row with Search & Filters -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3.5 pb-4 border-b border-[#dce0cd]">
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="w-2 h-2 rounded-full bg-[#c6ef4e]"></span>
                    <h2 class="text-base md:text-lg font-bold text-[#0e0f0c]">
                        Daftar Kategori Produk & Bahan
                    </h2>
                </div>
                <p class="text-xs text-[#737868]">
                    Kelola nama kategori, jenis klasifikasi barang, dan awalan kode SKU
                </p>
            </div>

            <!-- Search and Dropdowns -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Search Input -->
                <div class="relative flex-1 sm:w-72">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737868] text-[18px] select-none pointer-events-none">
                        search
                    </span>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama kategori, awalan SKU..."
                        class="w-full pl-9 pr-8 py-2 rounded-full bg-[#f2f4ec] text-[#0e0f0c] placeholder:text-[#737868] text-xs border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition-all"
                    />
                    <button
                        v-if="searchQuery"
                        type="button"
                        @click="searchQuery = ''"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-700 text-xs p-1 cursor-pointer"
                        title="Hapus pencarian"
                    >
                        ✕
                    </button>
                </div>

                <!-- Status Filter -->
                <div class="relative">
                    <select
                        v-model="statusFilter"
                        class="h-9 pl-3 pr-7 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-xs border border-[#dce0cd] focus:outline-none cursor-pointer font-medium"
                    >
                        <option value="all">Semua Status</option>
                        <option value="active">Kategori Aktif</option>
                        <option value="inactive">Kategori Nonaktif</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Filter Tabs (Pills) -->
        <div class="flex items-center gap-1.5 overflow-x-auto py-3 border-b border-[#dce0cd] mb-3">
            <button
                type="button"
                @click="activeTab = 'all'"
                :class="[
                    'px-4 py-1.5 rounded-full text-xs transition-all whitespace-nowrap cursor-pointer',
                    activeTab === 'all'
                        ? 'bg-[#0e0f0c] text-white font-bold shadow-xs'
                        : 'bg-[#f2f4ec] text-[#0e0f0c] hover:bg-[#eaecdf] font-medium'
                ]"
            >
                Semua ({{ props.counts.all || props.categories.length }})
            </button>

            <button
                type="button"
                @click="activeTab = 'finished'"
                :class="[
                    'px-4 py-1.5 rounded-full text-xs transition-all whitespace-nowrap cursor-pointer',
                    activeTab === 'finished'
                        ? 'bg-[#0e0f0c] text-white font-bold shadow-xs'
                        : 'bg-[#f2f4ec] text-[#0e0f0c] hover:bg-[#eaecdf] font-medium'
                ]"
            >
                Sepatu & Sandal Jadi ({{ props.counts.finished || 0 }})
            </button>

            <button
                type="button"
                @click="activeTab = 'raw'"
                :class="[
                    'px-4 py-1.5 rounded-full text-xs transition-all whitespace-nowrap cursor-pointer',
                    activeTab === 'raw'
                        ? 'bg-[#0e0f0c] text-white font-bold shadow-xs'
                        : 'bg-[#f2f4ec] text-[#0e0f0c] hover:bg-[#eaecdf] font-medium'
                ]"
            >
                Bahan Baku & Sol ({{ props.counts.raw || 0 }})
            </button>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto w-full">
            <table class="w-full min-w-[550px] text-left align-middle border-collapse text-xs">
                <thead>
                    <tr class="uppercase tracking-wider text-[#737868] border-b border-[#dce0cd] font-semibold text-[11px] select-none">
                        <th class="py-2.5 px-3">Nama Kategori & Awalan SKU</th>
                        <th class="py-2.5 px-3">Jenis Kategori</th>
                        <th class="py-2.5 px-3">Jumlah Barang</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#dce0cd]/60 text-[#0e0f0c]">
                    <tr
                        v-for="cat in filteredCategories"
                        :key="cat.id"
                        class="hover:bg-[#f2f4ec]/50 transition-colors group"
                    >
                        <!-- Kolom 1: Nama & Awalan SKU -->
                        <td class="py-3 px-3 align-middle">
                            <div class="flex items-center gap-2.5">
                                <div
                                    :class="[
                                        'w-8 h-8 rounded-xl flex items-center justify-center shrink-0 border shadow-2xs',
                                        cat.type === 'finished'
                                            ? 'bg-[#c6ef4e]/20 text-[#3b4d00] border-[#c6ef4e]/50'
                                            : 'bg-[#f2f4ec] text-[#0e0f0c] border-[#dce0cd]'
                                    ]"
                                >
                                    <span class="material-symbols-outlined text-[17px]">
                                        {{ cat.type === 'finished' ? 'roller_skating' : 'layers' }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs md:text-sm font-semibold text-[#0e0f0c]">
                                            {{ cat.name }}
                                        </p>
                                        <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-[#f2f4ec] text-[#0e0f0c] border border-[#dce0cd]">
                                            {{ cat.prefix_sku }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-[#737868] truncate max-w-xs md:max-w-md mt-0.5" :title="cat.description">
                                        {{ cat.description }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <!-- Kolom 2: Jenis Kategori -->
                        <td class="py-3 px-3 align-middle whitespace-nowrap">
                            <span
                                :class="[
                                    'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold',
                                    cat.type === 'finished'
                                        ? 'bg-[#c6ef4e]/30 text-[#3b4d00]'
                                        : 'bg-[#eeeee7] text-[#1a1c18]'
                                ]"
                            >
                                <span class="w-1.5 h-1.5 rounded-full" :class="cat.type === 'finished' ? 'bg-[#506600]' : 'bg-[#737868]'"></span>
                                <span>{{ cat.type_label }}</span>
                            </span>
                        </td>

                        <!-- Kolom 3: Jumlah Barang & Stok -->
                        <td class="py-3 px-3 align-middle whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold text-[#0e0f0c]">
                                    {{ cat.products_count }} Barang Terdaftar
                                </span>
                                <span class="text-[10px] text-[#737868]">
                                    Total stok fisik: {{ cat.total_stock }} unit
                                </span>
                            </div>
                        </td>

                        <!-- Kolom 4: Status -->
                        <td class="py-3 px-3 align-middle whitespace-nowrap">
                            <button
                                type="button"
                                @click="toggleCategoryStatus(cat)"
                                :class="[
                                    'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold transition cursor-pointer border',
                                    cat.is_active
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
                                        : 'bg-neutral-100 text-neutral-600 border-neutral-300 hover:bg-neutral-200'
                                ]"
                                :title="cat.is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan'"
                            >
                                <span class="w-1.5 h-1.5 rounded-full" :class="cat.is_active ? 'bg-emerald-600' : 'bg-neutral-400'"></span>
                                <span>{{ cat.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </button>
                        </td>

                        <!-- Kolom 5: Aksi -->
                        <td class="py-3 px-3 align-middle text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <!-- Edit -->
                                <button
                                    type="button"
                                    @click="emit('openEdit', cat)"
                                    class="p-1.5 rounded-full hover:bg-[#f2f4ec] text-[#737868] hover:text-[#0e0f0c] transition cursor-pointer"
                                    title="Edit Kategori"
                                >
                                    <span class="material-symbols-outlined text-[17px]">edit</span>
                                </button>

                                <!-- Hapus -->
                                <button
                                    type="button"
                                    @click="emit('openDelete', cat)"
                                    class="p-1.5 rounded-full hover:bg-rose-100 text-[#737868] hover:text-rose-600 transition cursor-pointer"
                                    title="Hapus Kategori"
                                >
                                    <span class="material-symbols-outlined text-[17px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="filteredCategories.length === 0">
                        <td colspan="5" class="py-10 text-center text-[#737868]">
                            <div class="flex flex-col items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-3xl text-neutral-300">category</span>
                                <p class="text-xs font-medium text-neutral-600">Tidak ada kategori yang cocok dengan pencarian.</p>
                                <button
                                    type="button"
                                    @click="searchQuery = ''; activeTab = 'all'; statusFilter = 'all'"
                                    class="text-xs text-[#0e0f0c] font-semibold underline mt-0.5 cursor-pointer"
                                >
                                    Reset Filter
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

