<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        required: true,
    },
    selectedProductId: {
        type: [Number, String, null],
        default: null,
    },
});

const emit = defineEmits(['select-product']);

const activeStatusFilter = ref('all');
const activeCategoryFilter = ref('all');
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 7;

// Kategori unik yang tersedia dari data produk
const availableCategories = computed(() => {
    const cats = new Set();
    props.products.forEach((p) => {
        if (p.category) cats.add(p.category);
    });
    return Array.from(cats);
});

// Reset page ketika filter berubah
watch([activeStatusFilter, activeCategoryFilter, searchQuery], () => {
    currentPage.value = 1;
});

const filteredProducts = computed(() => {
    return props.products.filter((prod) => {
        const matchStatus =
            activeStatusFilter.value === 'all' || prod.status === activeStatusFilter.value;

        const matchCategory =
            activeCategoryFilter.value === 'all' ||
            (activeCategoryFilter.value === 'sandal' &&
                (prod.category?.toLowerCase().includes('sandal') || prod.name?.toLowerCase().includes('sandal'))) ||
            (activeCategoryFilter.value === 'sepatu_pria' && prod.category === 'Sepatu Pria') ||
            (activeCategoryFilter.value === 'sepatu_wanita' && prod.category === 'Sepatu Wanita') ||
            (activeCategoryFilter.value === 'aksesoris' && prod.category?.toLowerCase().includes('aksesoris')) ||
            prod.category === activeCategoryFilter.value;

        const q = searchQuery.value.toLowerCase().trim();
        const matchSearch =
            !q ||
            prod.name.toLowerCase().includes(q) ||
            prod.category.toLowerCase().includes(q) ||
            prod.leakPoint.toLowerCase().includes(q) ||
            prod.sku.toLowerCase().includes(q);

        return matchStatus && matchCategory && matchSearch;
    });
});

const totalPages = computed(() => {
    return Math.ceil(filteredProducts.value.length / itemsPerPage) || 1;
});

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredProducts.value.slice(start, start + itemsPerPage);
});

const statusCount = computed(() => {
    return {
        all: props.products.length,
        kritis: props.products.filter((p) => p.status === 'kritis').length,
        waspada: props.products.filter((p) => p.status === 'waspada').length,
        sehat: props.products.filter((p) => p.status === 'sehat').length,
    };
});
</script>

<template>
    <div class="bg-white rounded-[26px] border border-[#dce0cd] shadow-xs overflow-hidden flex flex-col justify-between">
        <!-- Header Panel Kiri -->
        <div class="p-5 lg:p-6 border-b border-[#eaecdf] space-y-3.5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-xl font-black text-[#0e0f0c] tracking-tight">
                        Audit Laba Per Model Produk
                    </h3>
                    <p class="text-xs text-[#737868] mt-0.5 font-medium">
                        Klik baris produk untuk membedah rincian HPP, limbah, dan selisih diskon.
                    </p>
                </div>

                <!-- Status Filter Tabs -->
                <div class="flex items-center gap-1.5 bg-[#fafbf7] p-1 rounded-2xl border border-[#dce0cd] text-xs font-bold shrink-0 flex-wrap">
                    <button
                        type="button"
                        @click="activeStatusFilter = 'all'"
                        class="px-2.5 py-1 rounded-xl transition-all cursor-pointer"
                        :class="activeStatusFilter === 'all' ? 'bg-[#0e0f0c] text-white shadow-xs' : 'text-[#737868] hover:text-[#0e0f0c]'"
                    >
                        Semua ({{ statusCount.all }})
                    </button>
                    <button
                        type="button"
                        @click="activeStatusFilter = 'kritis'"
                        class="px-2.5 py-1 rounded-xl transition-all cursor-pointer flex items-center gap-1"
                        :class="activeStatusFilter === 'kritis' ? 'bg-rose-600 text-white shadow-xs' : 'text-rose-700 hover:bg-rose-50'"
                    >
                        <span>🔴</span> Kritis ({{ statusCount.kritis }})
                    </button>
                    <button
                        type="button"
                        @click="activeStatusFilter = 'waspada'"
                        class="px-2.5 py-1 rounded-xl transition-all cursor-pointer flex items-center gap-1"
                        :class="activeStatusFilter === 'waspada' ? 'bg-amber-500 text-white shadow-xs' : 'text-amber-700 hover:bg-amber-50'"
                    >
                        <span>🟡</span> Waspada ({{ statusCount.waspada }})
                    </button>
                    <button
                        type="button"
                        @click="activeStatusFilter = 'sehat'"
                        class="px-2.5 py-1 rounded-xl transition-all cursor-pointer flex items-center gap-1"
                        :class="activeStatusFilter === 'sehat' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-700 hover:bg-emerald-50'"
                    >
                        <span>🟢</span> Sehat ({{ statusCount.sehat }})
                    </button>
                </div>
            </div>

            <!-- Category Filter Pills & Search Bar -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-1">
                <!-- Category Pills -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-[11px] font-extrabold text-[#737868]">
                    <span class="text-[10px] uppercase tracking-wider text-neutral-400 font-bold shrink-0">Kategori:</span>
                    <button
                        type="button"
                        @click="activeCategoryFilter = 'all'"
                        class="px-2.5 py-1 rounded-lg transition-colors cursor-pointer shrink-0"
                        :class="activeCategoryFilter === 'all' ? 'bg-[#0e0f0c] text-[#c6ef4e]' : 'bg-[#fafbf7] border border-[#dce0cd] hover:bg-white'"
                    >
                        Semua
                    </button>
                    <button
                        type="button"
                        @click="activeCategoryFilter = 'sandal'"
                        class="px-2.5 py-1 rounded-lg transition-colors cursor-pointer shrink-0 flex items-center gap-1"
                        :class="activeCategoryFilter === 'sandal' ? 'bg-[#0e0f0c] text-[#c6ef4e]' : 'bg-[#fafbf7] border border-[#dce0cd] hover:bg-white'"
                    >
                        <span>🩴</span> Sandal Kulit & Karet
                    </button>
                    <button
                        type="button"
                        @click="activeCategoryFilter = 'sepatu_pria'"
                        class="px-2.5 py-1 rounded-lg transition-colors cursor-pointer shrink-0"
                        :class="activeCategoryFilter === 'sepatu_pria' ? 'bg-[#0e0f0c] text-[#c6ef4e]' : 'bg-[#fafbf7] border border-[#dce0cd] hover:bg-white'"
                    >
                        Sepatu Pria
                    </button>
                    <button
                        type="button"
                        @click="activeCategoryFilter = 'sepatu_wanita'"
                        class="px-2.5 py-1 rounded-lg transition-colors cursor-pointer shrink-0"
                        :class="activeCategoryFilter === 'sepatu_wanita' ? 'bg-[#0e0f0c] text-[#c6ef4e]' : 'bg-[#fafbf7] border border-[#dce0cd] hover:bg-white'"
                    >
                        Sepatu Wanita
                    </button>
                </div>

                <!-- Search Input -->
                <div class="relative sm:w-64 shrink-0">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari sandal, sepatu, SKU..."
                        class="w-full bg-[#fafbf7] border border-[#dce0cd] rounded-xl px-3 py-1.5 pl-8 text-xs text-[#0e0f0c] placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-[#8fae26]"
                    />
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-neutral-400 text-xs">
                        🔍
                    </span>
                    <button
                        v-if="searchQuery"
                        type="button"
                        @click="searchQuery = ''"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 text-xs cursor-pointer"
                    >
                        ✕
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#fafbf7] text-[#737868] border-b border-[#eaecdf] font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Model Sepatu / Sandal</th>
                        <th class="py-3 px-3 text-right">Target Margin</th>
                        <th class="py-3 px-3 text-right">Realisasi Riil</th>
                        <th class="py-3 px-4">Titik Bocor Utama</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eaecdf]">
                    <tr
                        v-for="prod in paginatedProducts"
                        :key="prod.id"
                        @click="emit('select-product', prod)"
                        class="cursor-pointer transition-all hover:bg-[#f4f6ee]/70 group"
                        :class="[
                            selectedProductId === prod.id ? 'bg-[#f4f6ee] ring-1 ring-[#8fae26] ring-inset' : ''
                        ]"
                    >
                        <!-- 1. Model Sepatu / Sandal -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-bold shrink-0"
                                    :class="[
                                        prod.status === 'kritis'
                                            ? 'bg-rose-100 text-rose-800'
                                            : prod.status === 'waspada'
                                                ? 'bg-amber-100 text-amber-800'
                                                : 'bg-emerald-100 text-emerald-800'
                                    ]"
                                >
                                    {{ prod.status === 'kritis' ? '🔴' : (prod.status === 'waspada' ? '🟡' : '🟢') }}
                                </div>
                                <div>
                                    <div class="font-extrabold text-[#0e0f0c] group-hover:text-[#2d4000] flex items-center gap-1.5">
                                        <span>{{ prod.name }}</span>
                                        <span class="text-[10px] text-neutral-400 group-hover:inline opacity-0 group-hover:opacity-100 transition-opacity">
                                            🔍 Bedah HPP
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-[#737868] flex items-center gap-1.5 mt-0.5">
                                        <span>{{ prod.category }}</span>
                                        <span>·</span>
                                        <span class="font-mono text-[10px]">{{ prod.sku }}</span>
                                        <span v-if="prod.currentStock > 0" class="text-neutral-400">· Stok: {{ prod.currentStock }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- 2. Target Margin -->
                        <td class="py-3.5 px-3 text-right">
                            <div class="font-bold text-[#0e0f0c]">
                                {{ prod.targetMargin }}
                            </div>
                            <div class="text-[10px] text-[#737868]">Katalog Standar</div>
                        </td>

                        <!-- 3. Realisasi Riil -->
                        <td class="py-3.5 px-3 text-right">
                            <div
                                class="font-extrabold"
                                :class="[
                                    prod.status === 'kritis'
                                        ? 'text-rose-600'
                                        : prod.status === 'waspada'
                                            ? 'text-amber-600'
                                            : 'text-emerald-700'
                                ]"
                            >
                                {{ prod.realMargin }}
                            </div>
                            <div class="text-[10px]" :class="prod.status === 'kritis' ? 'text-rose-600 font-bold' : 'text-[#737868]'">
                                <template v-if="prod.hppBreakdown?.realDealPrice < prod.hppBreakdown?.materialCost">
                                    🚨 Jual Rugi Modal!
                                </template>
                                <template v-else-if="prod.realMarginPct < prod.targetMarginPct">
                                    ⚠️ Turun -{{ (prod.targetMarginPct - prod.realMarginPct).toFixed(1) }}%
                                </template>
                                <template v-else>
                                    ✓ Sesuai Target
                                </template>
                            </div>
                        </td>

                        <!-- 4. Titik Bocor Utama -->
                        <td class="py-3.5 px-4">
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold"
                                :class="[
                                    prod.status === 'kritis'
                                        ? 'bg-rose-50 text-rose-800 border border-rose-200'
                                        : prod.status === 'waspada'
                                            ? 'bg-amber-50 text-amber-800 border border-amber-200'
                                            : 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                                ]"
                            >
                                {{ prod.leakPoint }}
                            </span>
                        </td>

                        <!-- 5. Status Badge -->
                        <td class="py-3.5 px-4 text-center">
                            <span
                                class="inline-block px-2.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wider"
                                :class="[
                                    prod.status === 'kritis'
                                        ? 'bg-rose-600 text-white'
                                        : prod.status === 'waspada'
                                            ? 'bg-amber-500 text-white'
                                            : 'bg-emerald-600 text-white'
                                ]"
                            >
                                {{ prod.status === 'kritis' ? '🔴 Kritis' : (prod.status === 'waspada' ? '🟡 Waspada' : '🟢 Sehat') }}
                            </span>
                        </td>
                    </tr>

                    <tr v-if="filteredProducts.length === 0">
                        <td colspan="5" class="py-8 text-center text-neutral-400">
                            Tidak ada model produk yang sesuai dengan filter audit.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer: Pagination & Hint -->
        <div class="p-3.5 bg-[#fafbf7] border-t border-[#eaecdf] flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-[#737868]">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#8fae26]"></span>
                <span>💡 <strong>Klik baris produk</strong> untuk membedah 5 komponen pembentuk HPP riil.</span>
            </div>

            <!-- Pagination Controls -->
            <div class="flex items-center gap-2 self-end sm:self-auto">
                <span class="font-bold text-[#0e0f0c]">
                    Halaman {{ currentPage }} dari {{ totalPages }} ({{ filteredProducts.length }} Model)
                </span>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        @click="currentPage = Math.max(1, currentPage - 1)"
                        :disabled="currentPage <= 1"
                        class="px-2.5 py-1 rounded-lg border border-[#dce0cd] bg-white font-bold cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed hover:bg-neutral-50 transition-colors"
                    >
                        ← Prev
                    </button>
                    <button
                        type="button"
                        @click="currentPage = Math.min(totalPages, currentPage + 1)"
                        :disabled="currentPage >= totalPages"
                        class="px-2.5 py-1 rounded-lg border border-[#dce0cd] bg-white font-bold cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed hover:bg-neutral-50 transition-colors"
                    >
                        Next →
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
