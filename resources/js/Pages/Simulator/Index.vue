<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import SidebarNav from '@/Components/SidebarNav.vue';
import LeakageSummaryCards from './Partials/LeakageSummaryCards.vue';
import ProductAuditTable from './Partials/ProductAuditTable.vue';
import ProductHppModal from './Partials/ProductHppModal.vue';
import ActionableInsights from './Partials/ActionableInsights.vue';
import SettingsModal from './Partials/SettingsModal.vue';

const props = defineProps({
    systemName: {
        type: String,
        default: 'Profit Leakage & Margin Guardian',
    },
    systemTagline: {
        type: String,
        default: 'Pusat Audit Kebocoran Laba & Pemandu Keputusan',
    },
    period: {
        type: String,
        default: '30_days',
    },
    periodOptions: {
        type: Array,
        default: () => [],
    },
    workshopInfo: {
        type: Object,
        default: () => ({}),
    },
    leakageCards: {
        type: Object,
        required: true,
    },
    productAudits: {
        type: Array,
        required: true,
    },
    actionableInsights: {
        type: Array,
        required: true,
    },
    baseline: {
        type: Object,
        default: () => ({}),
    },
});

// UI State
const showSettingsModal = ref(false);
const selectedProduct = ref(null);
const showHppModal = ref(false);

// Toast Notification State
const toastMessage = ref('');
const showToast = ref(false);
let toastTimer = null;

const triggerToast = (msg) => {
    toastMessage.value = msg;
    showToast.value = true;
    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        showToast.value = false;
    }, 3500);
};

// Filter Periode Audit Change
const onPeriodChange = (newPeriod) => {
    router.get(
        '/simulator',
        { period: newPeriod },
        { preserveState: false, preserveScroll: true }
    );
};

// Open HPP Modal for a Product
const handleSelectProduct = (product) => {
    selectedProduct.value = product;
    showHppModal.value = true;
};

// Action Executed Feedback
const handleActionExecuted = (payload) => {
    triggerToast(payload.message);
};

// Apply Action from HPP Modal
const handleApplyActionFromModal = (product) => {
    triggerToast(`Rekomendasi untuk "${product.name}" telah disiapkan di Panel Tindakan Konkret!`);
};
</script>

<template>
    <Head title="Profit Leakage & Margin Guardian · OptimaBiz" />

    <div class="min-h-screen w-full bg-[#eaecdf] p-2 md:p-5 flex flex-col md:flex-row gap-5 font-sans">
        <!-- Sidebar Navigation Rail -->
        <SidebarNav active="simulator" />

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col gap-5 min-w-0">
            <!-- Header Bar (Executive Bento Header) -->
            <header class="bg-white rounded-[28px] border border-[#dce0cd] p-5 sm:p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#0e0f0c] text-[#c6ef4e] text-xs font-black uppercase tracking-wider">
                            <span>🛡️</span> Auditor Internal Otomatis
                        </span>
                        <span class="text-xs text-neutral-400">·</span>
                        <span class="text-xs font-bold text-[#4d5240]">
                            👞 {{ props.workshopInfo?.name || 'OptimaBiz Leathercraft & Shoes' }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-[#0e0f0c] tracking-tight">
                        {{ props.systemName }}
                    </h1>
                    <p class="text-xs sm:text-sm text-[#737868] mt-0.5 font-medium">
                        {{ props.systemTagline }} — Membedah kebocoran laba riil dari catatan transaksi, meja potong kulit, dan gaji pengrajin.
                    </p>
                </div>

                <!-- Controls: Periode Audit & Pengaturan Bengkel -->
                <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                    <!-- Dropdown Periode Audit -->
                    <div class="relative">
                        <label for="periodAuditSelect" class="sr-only">Periode Audit</label>
                        <select
                            id="periodAuditSelect"
                            :value="props.period"
                            @change="onPeriodChange($event.target.value)"
                            class="bg-[#fafbf7] text-[#0e0f0c] border border-[#dce0cd] rounded-2xl px-4 py-2.5 text-xs sm:text-sm font-extrabold focus:outline-none focus:ring-2 focus:ring-[#8fae26] cursor-pointer appearance-none pr-9 shadow-2xs"
                        >
                            <option
                                v-for="opt in props.periodOptions"
                                :key="opt.id"
                                :value="opt.id"
                            >
                                {{ opt.label }}
                            </option>
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-neutral-400 text-xs">
                            ▼
                        </div>
                    </div>

                    <!-- Tombol Pengaturan Kapasitas & Beban Bengkel -->
                    <button
                        type="button"
                        @click="showSettingsModal = true"
                        title="Atur standar gaji tukang, sewa workshop, dan kapasitas produksi fisik"
                        class="p-2.5 sm:px-4 sm:py-2.5 rounded-2xl border border-[#dce0cd] bg-[#fafbf7] hover:bg-white text-[#0e0f0c] text-xs sm:text-sm font-bold flex items-center gap-2 cursor-pointer transition-all shadow-2xs shrink-0"
                    >
                        <svg class="w-4 h-4 text-[#737868]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="hidden sm:inline">Kapasitas Bengkel</span>
                    </button>
                </div>
            </header>

            <!-- 1. Baris Atas: 3 Kartu Deteksi Kebocoran Kas (Leakage Summary Cards) -->
            <LeakageSummaryCards
                :cards="props.leakageCards"
            />

            <!-- Bento Grid: Baris Bawah (Panel Kiri & Panel Kanan) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                <!-- 2. Panel Kiri: Tabel Audit Produk ("Mana Model yang Bocor?") (Col 7) -->
                <div class="lg:col-span-7">
                    <ProductAuditTable
                        :products="props.productAudits"
                        :selected-product-id="selectedProduct?.id || null"
                        @select-product="handleSelectProduct"
                    />
                </div>

                <!-- 3. Panel Kanan: Kotak Tindakan Konkret (Actionable Insights) (Col 5) -->
                <div class="lg:col-span-5">
                    <ActionableInsights
                        :insights="props.actionableInsights"
                        @action-executed="handleActionExecuted"
                    />
                </div>
            </div>

            <!-- Footer Informatif: Prinsip Auditor Lapangan -->
            <footer class="bg-white/80 backdrop-blur-xs rounded-2xl border border-[#dce0cd] px-5 py-3.5 text-xs text-[#737868] flex flex-wrap items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="font-bold text-[#0e0f0c] flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#8fae26]"></span>
                        Prinsip Audit OptimaBiz:
                    </span>
                    <span>1. Bukan Ramalan Abstrak (berdasarkan catatan transaksi riil)</span>
                    <span class="text-neutral-300">·</span>
                    <span>2. Terintegrasi Tanpa Input Rumit</span>
                    <span class="text-neutral-300">·</span>
                    <span>3. Bahasa Operasional Bengkel Lapangan</span>
                </div>

                <div class="text-[11px] text-[#4d5240] font-semibold">
                    Staf Aktif: {{ props.workshopInfo?.employeeCount || 5 }} Tukang | Kapasitas: {{ props.workshopInfo?.monthlyMaxCapacity || 500 }} psg/bln
                </div>
            </footer>
        </main>

        <!-- Product HPP Breakdown Modal (Muncul saat baris produk diklik) -->
        <ProductHppModal
            :show="showHppModal"
            :product="selectedProduct"
            @close="showHppModal = false"
            @apply-action="handleApplyActionFromModal"
        />

        <!-- Settings Modal (Atur Tukang, Gaji, Beban Tetap, Kapasitas) -->
        <SettingsModal
            :show="showSettingsModal"
            :baseline="props.baseline"
            @close="showSettingsModal = false"
        />

        <!-- Floating Toast Notification -->
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showToast"
                class="fixed bottom-5 right-5 z-50 bg-[#0e0f0c] text-white px-4 py-3 rounded-2xl shadow-2xl border border-[#c6ef4e] flex items-center gap-3 max-w-md text-xs font-bold"
            >
                <span class="text-base text-[#c6ef4e]">✨</span>
                <span>{{ toastMessage }}</span>
                <button
                    type="button"
                    @click="showToast = false"
                    class="ml-auto text-neutral-400 hover:text-white cursor-pointer"
                >
                    ✕
                </button>
            </div>
        </transition>
    </div>
</template>
