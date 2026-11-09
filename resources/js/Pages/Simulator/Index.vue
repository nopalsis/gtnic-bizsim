<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import SidebarNav from '@/Components/SidebarNav.vue';
import LeakageSummaryCards from './Partials/LeakageSummaryCards.vue';
import ProductAuditTable from './Partials/ProductAuditTable.vue';
import ProductHppModal from './Partials/ProductHppModal.vue';
import ActionableInsights from './Partials/ActionableInsights.vue';
import AllActionsModal from './Partials/AllActionsModal.vue';

const props = defineProps({
    systemName: {
        type: String,
        default: 'Profit Leakage & Margin Guardian',
    },
    systemTagline: {
        type: String,
        default: 'Audit Kebocoran Laba & Pemandu Keputusan',
    },
    pageTitle: {
        type: String,
        default: 'Audit Kebocoran Laba',
    },
    pageSubtitle: {
        type: String,
        default: 'Pantau laba yang hilang akibat diskon berlebih, limbah kulit, dan stok mandek.',
    },
    period: {
        type: String,
        default: '30_days',
    },
    periodLabel: {
        type: String,
        default: '30 Hari Terakhir',
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
const showActionsModal = ref(false);
const selectedProduct = ref(null);
const showHppModal = ref(false);

// State Tindakan yang telah dijalankan (disinkronisasi antara panel samping & modal)
const executedActions = ref({
    floor_price_guardrail: false,
    smart_craftsman_assignment: false,
    cash_liquidation_clearance: false,
});

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
const handleToggleAction = (insight) => {
    executedActions.value[insight.id] = !executedActions.value[insight.id];
    const isNowActive = executedActions.value[insight.id];
    triggerToast(
        isNowActive
            ? `${insight.activeStatus} berhasil diaktifkan!`
            : `Status ${insight.title} dinonaktifkan.`
    );
};

// Copy Instruction Feedback
const handleCopySingleInstruction = (insight) => {
    triggerToast(`Instruksi "${insight.title}" berhasil disalin ke clipboard!`);
};

const handleCopyAllBriefing = () => {
    triggerToast('Seluruh instruksi & SOP tindakan workshop berhasil disalin ke clipboard!');
};

// Apply Action from HPP Modal
const handleApplyActionFromModal = (product) => {
    showActionsModal.value = true;
    triggerToast(`Membuka Pusat Tindakan untuk model "${product.name}"...`);
};
</script>

<template>
    <Head title="Profit Leakage & Margin Guardian · OptimaBiz" />

    <div class="min-h-screen w-full bg-[#eaecdf] p-2 md:p-5 flex flex-col md:flex-row gap-5 font-sans">
        <!-- Sidebar Navigation Rail -->
        <SidebarNav active="simulator" />

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col gap-5 min-w-0">
            <!-- Header Bar -->
            <header class="bg-white rounded-[28px] border border-[#dce0cd] p-5 sm:p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-[#f4f6ee] text-[#4d5240] text-xs font-bold border border-[#eaecdf]">
                            👞 {{ props.workshopInfo?.name || 'OptimaBiz Workshop' }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-[#0e0f0c] tracking-tight">
                        {{ props.pageTitle }}
                    </h1>
                    <p class="text-xs sm:text-sm text-[#737868] mt-1 font-medium">
                        {{ props.pageSubtitle }}
                    </p>
                </div>

                <!-- Controls: Periode Audit & Rekomendasi Tindakan -->
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

                    <!-- Tombol Buka Rekomendasi Tindakan Lengkap -->
                    <button
                        type="button"
                        @click="showActionsModal = true"
                        class="px-4 py-2.5 rounded-2xl bg-[#0e0f0c] text-[#c6ef4e] hover:bg-[#2d4000] text-xs sm:text-sm font-black flex items-center gap-2 cursor-pointer transition-all shadow-xs shrink-0"
                    >
                        <span>⚡ Rekomendasi Tindakan</span>
                        <span class="px-1.5 py-0.2 rounded-full bg-[#c6ef4e] text-[#0e0f0c] text-[10px] font-black">
                            {{ props.actionableInsights.length }}
                        </span>
                    </button>
                </div>
            </header>

            <!-- 1. Baris Atas: 3 Kartu Deteksi Kebocoran Kas -->
            <LeakageSummaryCards
                :cards="props.leakageCards"
                :period-label="props.periodLabel"
            />

            <!-- 2. Tabel Audit Produk ("Mana Model yang Bocor?") (Full Width) -->
            <section class="w-full">
                <ProductAuditTable
                    :products="props.productAudits"
                    :selected-product-id="selectedProduct?.id || null"
                    @select-product="handleSelectProduct"
                />
            </section>

            <!-- 3. Kotak Tindakan Konkret (Actionable Insights) (Berada di Bawah Tabel) -->
            <section class="w-full">
                <ActionableInsights
                    :insights="props.actionableInsights"
                    :executed-actions="executedActions"
                    @toggle-action="handleToggleAction"
                    @open-modal="showActionsModal = true"
                />
            </section>

            <!-- Footer Informatif -->
            <footer class="bg-white/80 backdrop-blur-xs rounded-2xl border border-[#dce0cd] px-5 py-3 text-xs text-[#737868] flex flex-wrap items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#8fae26]"></span>
                    <span class="font-medium text-[#4d5240]">Data transaksi & limbah terintegrasi otomatis dari catatan bengkel.</span>
                </div>

                <div class="text-[11px] text-[#4d5240] font-semibold">
                    Staf Pengrajin Aktif: {{ props.workshopInfo?.employeeCount || 5 }} Orang
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

        <!-- All Actions Hub Modal (Pusat Rekomendasi & Tindakan Lengkap) -->
        <AllActionsModal
            :show="showActionsModal"
            :insights="props.actionableInsights"
            :executed-actions="executedActions"
            @close="showActionsModal = false"
            @toggle-action="handleToggleAction"
            @copy-instruction="handleCopySingleInstruction"
            @copy-all="handleCopyAllBriefing"
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
