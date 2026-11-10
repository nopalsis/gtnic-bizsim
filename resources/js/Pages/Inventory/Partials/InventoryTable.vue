<script setup>
import { ref, computed, watch } from 'vue';
import PaginationNav from '@/Components/PaginationNav.vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['openMutation', 'openEdit', 'deleteProduct']);

// State Search & Filter
const searchQuery = ref('');
const statusFilter = ref('all'); // 'all' | 'safe' | 'low'
const sortOrder = ref('latest'); // 'latest' | 'highest_val' | 'lowest_stock' | 'name_asc'
const activeCategoryTab = ref('all'); // 'all' | 'finished' | 'raw'

// Pagination State
const currentPage = ref(1);
const itemsPerPage = ref(8);

const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};

// Helper Icon Kategori
const getCategoryIcon = (category) => {
    const cat = (category || '').toLowerCase();
    if (cat.includes('kulit') && !cat.includes('sepatu') && !cat.includes('sandal')) return 'layers';
    if (cat.includes('sol')) return 'texture';
    if (cat.includes('pendukung') || cat.includes('lem') || cat.includes('kimia')) return 'sanitizer';
    if (cat.includes('sepatu')) return 'roller_skating';
    if (cat.includes('sandal')) return 'hiking';
    return 'inventory_2';
};

// Filtered & Sorted Products
const filteredProducts = computed(() => {
    let result = [...props.products];

    // Filter Kategori Tab
    if (activeCategoryTab.value === 'finished') {
        result = result.filter((p) => p.category_type === 'finished');
    } else if (activeCategoryTab.value === 'raw') {
        result = result.filter((p) => p.category_type === 'raw');
    }

    // Filter Status Stok
    if (statusFilter.value === 'safe') {
        result = result.filter((p) => !p.is_low_stock);
    } else if (statusFilter.value === 'low') {
        result = result.filter((p) => p.is_low_stock);
    }

    // Filter Pencarian Teks
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase().trim();
        result = result.filter(
            (p) =>
                p.name?.toLowerCase().includes(q) ||
                p.sku?.toLowerCase().includes(q) ||
                p.category?.toLowerCase().includes(q)
        );
    }

    // Pengurutan
    if (sortOrder.value === 'highest_val') {
        result.sort((a, b) => (b.valuation || 0) - (a.valuation || 0));
    } else if (sortOrder.value === 'lowest_stock') {
        result.sort((a, b) => a.current_stock - b.current_stock);
    } else if (sortOrder.value === 'name_asc') {
        result.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
    } else if (sortOrder.value === 'latest') {
        result.sort((a, b) => b.id - a.id);
    }

    return result;
});

// Paginasi
const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredProducts.value.length / itemsPerPage.value));
});

watch(totalPages, (max) => {
    if (currentPage.value > max) currentPage.value = max;
});

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredProducts.value.slice(start, start + itemsPerPage.value);
});

const showingStart = computed(() => {
    if (filteredProducts.value.length === 0) return 0;
    return (currentPage.value - 1) * itemsPerPage.value + 1;
});

const showingEnd = computed(() => {
    return Math.min(currentPage.value * itemsPerPage.value, filteredProducts.value.length);
});

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

// Hitung rekap tab
const finishedCount = computed(() => props.products.filter((p) => p.category_type === 'finished').length);
const rawCount = computed(() => props.products.filter((p) => p.category_type === 'raw').length);
</script>

<template>
    <section class="bg-white rounded-2xl md:rounded-[24px] p-3.5 sm:p-4 md:p-6 shadow-xs border border-[#dce0cd] overflow-hidden flex flex-col">
        <!-- Toolbar Header Row with Search & Dropdowns -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3.5 pb-4 border-b border-[#dce0cd]">
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="w-2 h-2 rounded-full bg-[#c6ef4e]"></span>
                    <h2 class="text-base md:text-lg font-bold text-[#0e0f0c]">
                        Daftar Stok Sepatu & Bahan
                    </h2>
                </div>
                <p class="text-xs text-[#737868]">
                    Catatan jumlah stok barang, harga modal, dan status ketersediaan
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
                        placeholder="Cari nama barang, SKU..."
                        class="w-full pl-9 pr-8 py-2 rounded-full bg-[#f2f4ec] text-[#0e0f0c] placeholder:text-[#737868] text-xs border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition-all"
                        @input="currentPage = 1"
                    />
                    <button
                        v-if="searchQuery"
                        type="button"
                        @click="searchQuery = ''; currentPage = 1"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-700 text-xs p-1"
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
                        @change="currentPage = 1"
                    >
                        <option value="all">Semua Status</option>
                        <option value="safe">Stok Aman</option>
                        <option value="low">Stok Kritis</option>
                    </select>
                </div>

                <!-- Sort Filter -->
                <div class="relative">
                    <select
                        v-model="sortOrder"
                        class="h-9 pl-3 pr-7 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-xs border border-[#dce0cd] focus:outline-none cursor-pointer font-medium"
                        @change="currentPage = 1"
                    >
                        <option value="latest">Terbaru</option>
                        <option value="highest_val">Nilai Tertinggi</option>
                        <option value="lowest_stock">Stok Terendah</option>
                        <option value="name_asc">Nama (A - Z)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Filter Tabs (Pills) -->
        <div class="flex items-center gap-1.5 overflow-x-auto py-3 border-b border-[#dce0cd] mb-3">
            <button
                type="button"
                @click="activeCategoryTab = 'all'; currentPage = 1"
                :class="[
                    'px-4 py-1.5 rounded-full text-xs transition-all whitespace-nowrap cursor-pointer',
                    activeCategoryTab === 'all'
                        ? 'bg-[#0e0f0c] text-white font-bold shadow-xs'
                        : 'bg-[#f2f4ec] text-[#0e0f0c] hover:bg-[#eaecdf] font-medium'
                ]"
            >
                Semua ({{ props.products.length }})
            </button>

            <button
                type="button"
                @click="activeCategoryTab = 'finished'; currentPage = 1"
                :class="[
                    'px-4 py-1.5 rounded-full text-xs transition-all whitespace-nowrap cursor-pointer',
                    activeCategoryTab === 'finished'
                        ? 'bg-[#0e0f0c] text-white font-bold shadow-xs'
                        : 'bg-[#f2f4ec] text-[#0e0f0c] hover:bg-[#eaecdf] font-medium'
                ]"
            >
                Produk Jadi ({{ finishedCount }})
            </button>

            <button
                type="button"
                @click="activeCategoryTab = 'raw'; currentPage = 1"
                :class="[
                    'px-4 py-1.5 rounded-full text-xs transition-all whitespace-nowrap cursor-pointer',
                    activeCategoryTab === 'raw'
                        ? 'bg-[#0e0f0c] text-white font-bold shadow-xs'
                        : 'bg-[#f2f4ec] text-[#0e0f0c] hover:bg-[#eaecdf] font-medium'
                ]"
            >
                Bahan Baku & Sol ({{ rawCount }})
            </button>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto w-full">
            <table class="w-full min-w-[620px] text-left align-middle border-collapse text-xs">
                <thead>
                    <tr class="uppercase tracking-wider text-[#737868] border-b border-[#dce0cd] font-semibold text-[11px] select-none">
                        <th class="py-2.5 px-3">Nama & Model</th>
                        <th class="py-2.5 px-3">Harga Modal & Jual</th>
                        <th class="py-2.5 px-3">Stok Sekarang</th>
                        <th class="py-2.5 px-3">Batas Minimum</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#dce0cd]/60 text-[#0e0f0c]">
                    <tr
                        v-for="prod in paginatedProducts"
                        :key="prod.id"
                        class="hover:bg-[#f2f4ec]/50 transition-colors group"
                    >
                        <!-- Kolom 1: Nama & Kategori -->
                        <td class="py-3 px-3 align-middle">
                            <div class="flex items-center gap-2.5">
                                <div
                                    :class="[
                                        'w-8 h-8 rounded-xl flex items-center justify-center shrink-0 border shadow-2xs',
                                        prod.is_low_stock
                                            ? 'bg-rose-50 text-rose-600 border-rose-200'
                                            : 'bg-[#f2f4ec] text-[#0e0f0c] border-[#dce0cd]'
                                    ]"
                                >
                                    <span class="material-symbols-outlined text-[17px]">{{ getCategoryIcon(prod.category) }}</span>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[10px] text-[#737868] uppercase font-semibold block leading-tight">
                                        {{ prod.sku }}
                                    </span>
                                    <p class="text-xs md:text-sm font-semibold text-[#0e0f0c] truncate max-w-[200px] md:max-w-xs" :title="prod.name">
                                        {{ prod.name }}
                                    </p>
                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#f2f4ec] text-[#737868]">
                                        {{ prod.category }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <!-- Kolom 2: Modal (HPP) & Harga Jual -->
                        <td class="py-3 px-3 align-middle whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-xs text-[#0e0f0c] font-semibold">
                                    {{ formatRupiah(prod.cost_price) }}
                                    <span class="text-[10px] font-normal text-[#737868]">/ {{ prod.unit }}</span>
                                </span>
                                <span v-if="prod.category_type === 'finished'" class="text-[10px] text-[#737868]">
                                    Jual: {{ formatRupiah(prod.base_price) }}
                                </span>
                                <span v-else class="text-[10px] text-[#737868] italic font-medium">
                                    Bahan Baku (Tidak Dijual)
                                </span>
                            </div>
                        </td>

                        <!-- Kolom 3: Stok Saat Ini (Ukurannya proporsional) -->
                        <td class="py-3 px-3 align-middle whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <span
                                    :class="[
                                        'text-xs md:text-sm font-bold',
                                        prod.is_low_stock ? 'text-rose-600' : 'text-[#0e0f0c]'
                                    ]"
                                >
                                    {{ prod.current_stock }}
                                </span>
                                <span class="text-xs text-[#737868]">{{ prod.unit }}</span>

                                <span
                                    v-if="prod.is_low_stock"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 border border-rose-200 text-[10px] font-semibold ml-1.5"
                                >
                                    <span class="material-symbols-outlined text-[12px]">warning</span>
                                    Sisa {{ prod.current_stock }} (Hampir Habis)
                                </span>

                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#c6ef4e]/30 text-[#3b4d00] text-[10px] font-semibold ml-1.5"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#506600]"></span>
                                    Stok Cukup (Aman)
                                </span>
                            </div>
                        </td>

                        <!-- Kolom 4: Batas Minimum -->
                        <td class="py-3 px-3 align-middle whitespace-nowrap">
                            <span class="text-xs text-[#737868]">
                                Min: {{ prod.min_stock_alert }} {{ prod.unit }}
                            </span>
                        </td>

                        <!-- Kolom 5: Aksi -->
                        <td class="py-3 px-3 align-middle text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <button
                                    type="button"
                                    @click="emit('openMutation', prod)"
                                    class="px-2.5 py-1 rounded-full bg-[#f2f4ec] hover:bg-[#0e0f0c] hover:text-white text-[#0e0f0c] text-[11px] font-semibold transition border border-[#dce0cd] cursor-pointer"
                                >
                                    Mutasi (+/-)
                                </button>
                                <button
                                    type="button"
                                    @click="emit('openEdit', prod)"
                                    class="p-1 rounded-full hover:bg-[#f2f4ec] text-[#737868] hover:text-[#0e0f0c] transition cursor-pointer"
                                    title="Edit Barang"
                                >
                                    <span class="material-symbols-outlined text-[16px]">edit</span>
                                </button>
                                <button
                                    type="button"
                                    @click="emit('deleteProduct', prod)"
                                    class="p-1 rounded-full hover:bg-rose-100 text-[#737868] hover:text-rose-600 transition cursor-pointer"
                                    title="Hapus / Nonaktifkan"
                                >
                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="filteredProducts.length === 0">
                        <td colspan="5" class="py-10 text-center text-[#737868]">
                            <div class="flex flex-col items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-3xl text-neutral-300">inventory_2</span>
                                <p class="text-xs font-medium text-neutral-600">Tidak ada item inventaris yang sesuai kriteria.</p>
                                <button
                                    type="button"
                                    @click="searchQuery = ''; statusFilter = 'all'; activeCategoryTab = 'all'; currentPage = 1"
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

        <!-- Pagination Footer -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 pt-3 border-t border-[#dce0cd]">
            <span class="text-xs text-[#737868] text-center sm:text-left">
                Menampilkan <strong class="text-[#0e0f0c]">{{ showingStart }}-{{ showingEnd }}</strong> dari
                <strong class="text-[#0e0f0c]">{{ filteredProducts.length }}</strong> total inventaris terdaftar
            </span>

            <PaginationNav
                variant="pill"
                :current-page="currentPage"
                :total-pages="totalPages"
                @change="goToPage"
            />
        </div>
    </section>
</template>
