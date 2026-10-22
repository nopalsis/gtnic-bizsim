<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import SidebarNav from '@/Components/SidebarNav.vue';
import TransactionLedgerModal from '@/Components/TransactionLedgerModal.vue';

const props = defineProps({
    kpis: Object,
    capacity: Object,
    categorySales: Array,
    costDistribution: Array,
    recentTransactions: Array,
    allTransactions: Array,
    recipients: Array,
    topProducts: Array,
    trendHistory: Array,
    inventoryStats: Object,
    staffPerformance: Array,
    healthScore: Object,
    alerts: Array,
});

// UI State
const showLedgerModal = ref(false);
const hoveredTrendPoint = ref(null);
const activeBottomTab = ref('topProducts'); // 'topProducts' | 'recentTx'

// Currency Formatter
const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};

// Dynamic Bézier Trend Line Chart Computed from Database
const trendDataPoints = computed(() => {
    if (!props.trendHistory || props.trendHistory.length === 0) return [];

    const items = props.trendHistory;
    const amounts = items.map((t) => t.amount);
    const maxVal = Math.max(...amounts, 600000);
    const minVal = Math.min(...amounts, 0);
    const range = maxVal - minVal || 1;

    const width = 500;
    const height = 140;
    const topPadding = 20;
    const bottomPadding = 20;
    const usableHeight = height - topPadding - bottomPadding;

    return items.map((item, idx) => {
        const x = Math.round((idx / (items.length - 1 || 1)) * width + 5);
        const y = Math.round(height - bottomPadding - ((item.amount - minVal) / range) * usableHeight);
        return {
            ...item,
            x,
            y,
        };
    });
});

const bezierPathD = computed(() => {
    const points = trendDataPoints.value;
    if (points.length < 2) return '';

    let d = `M ${points[0].x},${points[0].y}`;
    for (let i = 0; i < points.length - 1; i++) {
        const p0 = i > 0 ? points[i - 1] : points[0];
        const p1 = points[i];
        const p2 = points[i + 1];
        const p3 = i < points.length - 2 ? points[i + 2] : p2;

        const cp1x = p1.x + (p2.x - p0.x) / 6;
        const cp1y = p1.y + (p2.y - p0.y) / 6;
        const cp2x = p2.x - (p3.x - p1.x) / 6;
        const cp2y = p2.y - (p3.y - p1.y) / 6;

        d += ` C ${Math.round(cp1x)},${Math.round(cp1y)} ${Math.round(cp2x)},${Math.round(cp2y)} ${p2.x},${p2.y}`;
    }
    return d;
});
</script>

<template>
    <Head title="Executive Dashboard · GTNIC BizSim" />

    <div class="min-h-screen w-full bg-[#eaecdf] p-2 md:p-5 flex flex-col md:flex-row gap-5 font-sans">
        <!-- Sidebar Navigation Rail -->
        <SidebarNav active="dashboard" />

        <!-- Main Dashboard Content -->
        <main class="flex-1 flex flex-col gap-5 min-w-0">
            <!-- Header Section -->
            <header class="bg-white/80 backdrop-blur-md rounded-[24px] p-5 border border-[#dce0cd] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#c6ef4e] border border-[#a2c833]"></span>
                        <h1 class="text-xl font-black text-[#0e0f0c] tracking-tight">GTNIC Flagship Café</h1>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-600">Simulasi Bisnis</span>
                    </div>
                    <p class="text-xs text-neutral-500 font-medium mt-1">Ringkasan performa finansial & operasional gerai real-time</p>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
                    <!-- Health Score Badge -->
                    <div class="flex items-center gap-2 bg-[#f4f5ee] px-3.5 py-1.5 rounded-2xl border border-[#e1e5d3]">
                        <div class="text-right">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-neutral-400">Skor Bisnis</div>
                            <div class="text-xs font-black text-[#0e0f0c]">{{ props.healthScore?.grade || 'A-' }} · {{ props.healthScore?.score || 88 }} Poin</div>
                        </div>
                        <div class="w-7 h-7 rounded-full bg-[#0e0f0c] text-[#c6ef4e] font-black text-xs flex items-center justify-center">
                            {{ props.healthScore?.grade?.charAt(0) || 'A' }}
                        </div>
                    </div>

                    <!-- Open Ledger Modal CTA -->
                    <button
                        type="button"
                        @click="showLedgerModal = true"
                        class="bg-[#0e0f0c] hover:bg-neutral-800 text-white text-xs font-bold px-4 py-2.5 rounded-2xl transition flex items-center gap-1.5 shadow-sm cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-[#c6ef4e]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span>Buku Besar</span>
                    </button>
                </div>
            </header>

            <!-- 4 Executive KPI Scorecards -->
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" aria-label="KPI Finansial">
                <!-- KPI 1: Omzet Bersih & Kotor -->
                <div class="bg-white rounded-[22px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-wider text-neutral-400">Omzet Bersih</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Diskon {{ props.kpis?.discountRate || 0 }}%
                        </span>
                    </div>
                    <div class="text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                        {{ formatRupiah(props.kpis?.netRevenue) }}
                    </div>
                    <div class="text-[11px] text-neutral-500 font-medium mt-1 truncate">
                        Kotor: {{ formatRupiah(props.kpis?.grossRevenue) }}
                    </div>
                </div>

                <!-- KPI 2: Laba Kotor & Margin -->
                <div class="bg-white rounded-[22px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-wider text-neutral-400">Laba Kotor (Gross)</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#c6ef4e]/30 text-[#0e0f0c] border border-[#a2c833]">
                            Margin {{ props.kpis?.profitMargin || 0 }}%
                        </span>
                    </div>
                    <div class="text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                        {{ formatRupiah(props.kpis?.grossProfit) }}
                    </div>
                    <div class="text-[11px] text-neutral-500 font-medium mt-1 truncate">
                        HPP Bahan: {{ formatRupiah(props.costDistribution?.[0]?.amount) }}
                    </div>
                </div>

                <!-- KPI 3: Beban Operasional Tetap -->
                <div class="bg-white rounded-[22px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-wider text-neutral-400">Beban Tetap (Opex)</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-stone-100 text-stone-700">
                            4 Karyawan + Sewa
                        </span>
                    </div>
                    <div class="text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                        {{ formatRupiah(props.kpis?.totalFixedOpex) }}
                    </div>
                    <div class="text-[11px] text-neutral-500 font-medium mt-1 truncate">
                        Laba Bersih: {{ formatRupiah(props.kpis?.netProfit) }}
                    </div>
                </div>

                <!-- KPI 4: Target Impas (BEP) -->
                <div class="bg-white rounded-[22px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-wider text-neutral-400">Target Impas (BEP)</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200">
                            Kapasitas {{ props.capacity?.usagePercent || 0 }}%
                        </span>
                    </div>
                    <div class="text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                        {{ formatRupiah(props.kpis?.bepRevenue) }}
                    </div>
                    <div class="text-[11px] text-neutral-500 font-medium mt-1 truncate">
                        Terjual: {{ props.capacity?.soldUnits || 0 }} / {{ props.capacity?.maxCapacity || 5000 }} Unit
                    </div>
                </div>
            </section>

            <!-- Middle Section: Sales Trend & Category Distribution -->
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
                <!-- Trend Omzet Harian (Bézier Chart) - Col 7 -->
                <div class="lg:col-span-7 bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-base font-black text-[#0e0f0c]">Tren Penjualan Harian</h2>
                            <p class="text-[11px] text-neutral-400 font-medium">Berdasarkan 20 rekaman transaksi riil dari database</p>
                        </div>
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-[#f4f5ee] text-[#0e0f0c] border border-[#e1e5d3]">
                            {{ props.trendHistory?.length || 0 }} Hari Terdata
                        </span>
                    </div>

                    <!-- Bézier SVG Curve -->
                    <div class="relative w-full h-44 mt-2">
                        <!-- Y-Scale Labels -->
                        <div class="absolute left-0 top-0 bottom-6 flex flex-col justify-between text-[10px] font-bold text-neutral-400 tabular-nums">
                            <span>500k</span>
                            <span>250k</span>
                            <span>0k</span>
                        </div>

                        <div class="ml-9 h-full relative">
                            <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 510 140">
                                <!-- Guide lines -->
                                <line stroke="#f1f3eb" stroke-dasharray="3 3" stroke-width="1.2" x1="0" x2="510" y1="20" y2="20"></line>
                                <line stroke="#f1f3eb" stroke-dasharray="3 3" stroke-width="1.2" x1="0" x2="510" y1="75" y2="75"></line>
                                <line stroke="#f1f3eb" stroke-width="1.2" x1="0" x2="510" y1="120" y2="120"></line>

                                <!-- Bézier Curve -->
                                <path
                                    :d="bezierPathD"
                                    fill="none"
                                    stroke="#0e0f0c"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2.5"
                                ></path>

                                <!-- Points -->
                                <g
                                    v-for="(pt, idx) in trendDataPoints"
                                    :key="'pt-' + idx"
                                    @mouseenter="hoveredTrendPoint = pt"
                                    @mouseleave="hoveredTrendPoint = null"
                                    class="cursor-pointer"
                                >
                                    <circle
                                        :cx="pt.x"
                                        :cy="pt.y"
                                        :r="hoveredTrendPoint?.full_date === pt.full_date ? 6 : 3"
                                        :fill="hoveredTrendPoint?.full_date === pt.full_date ? '#000000' : '#ffffff'"
                                        stroke="#c6ef4e"
                                        stroke-width="2.2"
                                        class="transition-all duration-150"
                                    ></circle>
                                </g>
                            </svg>

                            <!-- Tooltip Pill -->
                            <div
                                v-if="hoveredTrendPoint"
                                class="absolute -top-3 bg-[#0e0f0c] text-white text-[11px] font-bold px-3 py-1 rounded-full shadow-xl border border-neutral-700 pointer-events-none whitespace-nowrap z-20 flex items-center gap-1.5 transition-all"
                                :style="{ left: `${hoveredTrendPoint.x}px`, transform: 'translateX(-50%)' }"
                            >
                                <span class="text-[#c6ef4e]">{{ hoveredTrendPoint.date }}:</span>
                                <span>{{ formatRupiah(hoveredTrendPoint.amount) }}</span>
                                <span class="text-neutral-400">({{ hoveredTrendPoint.count }} tx)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bauran Penjualan Kategori - Col 5 -->
                <div class="lg:col-span-5 bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h2 class="text-base font-black text-[#0e0f0c]">Bauran Penjualan Kategori</h2>
                                <p class="text-[11px] text-neutral-400 font-medium">Kontribusi tiap kategori terhadap total omzet</p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#c6ef4e]/30 text-[#0e0f0c]">
                                4 Kategori
                            </span>
                        </div>

                        <!-- Category Progress Bars -->
                        <div class="space-y-3 mt-4">
                            <div v-for="cat in props.categorySales" :key="cat.category" class="space-y-1">
                                <div class="flex items-center justify-between text-xs font-bold text-[#0e0f0c]">
                                    <span>{{ cat.category }}</span>
                                    <span class="tabular-nums">{{ formatRupiah(cat.sales) }} ({{ cat.percentage }}%)</span>
                                </div>
                                <div class="w-full bg-[#f4f5ee] h-2.5 rounded-full overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :class="{
                                            'bg-amber-500': cat.category === 'Beverage',
                                            'bg-emerald-600': cat.category === 'Merchandise',
                                            'bg-stone-700': cat.category === 'Coffee Beans',
                                            'bg-orange-500': cat.category === 'Food',
                                        }"
                                        :style="{ width: `${cat.percentage}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cost Distribution Summary Footer -->
                    <div class="pt-4 mt-4 border-t border-neutral-100 flex items-center justify-between text-[11px] text-neutral-500">
                        <span>HPP: {{ formatRupiah(props.costDistribution?.[0]?.amount) }}</span>
                        <span>Waste: {{ formatRupiah(props.kpis?.wasteCost) }}</span>
                        <span>Opex: {{ formatRupiah(props.kpis?.totalFixedOpex) }}</span>
                    </div>
                </div>
            </section>

            <!-- Bottom Section: Actionable Monitoring & Menu Terlaris / Transaksi -->
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
                <!-- Monitoring & Alerts - Col 5 -->
                <div class="lg:col-span-5 bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-base font-black text-[#0e0f0c]">Pengawasan & Mitigasi Risiko</h2>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200">
                                Waste: {{ formatRupiah(props.kpis?.wasteCost) }}
                            </span>
                        </div>

                        <!-- Alerts List -->
                        <div class="space-y-3 mt-3">
                            <div
                                v-for="(alert, idx) in props.alerts"
                                :key="idx"
                                class="p-3.5 rounded-2xl border text-xs leading-relaxed"
                                :class="{
                                    'bg-rose-50/50 border-rose-200 text-rose-900': alert.type === 'risk',
                                    'bg-amber-50/50 border-amber-200 text-amber-900': alert.type === 'warning' || alert.type === 'target',
                                    'bg-[#f4f5ee] border-[#e1e5d3] text-neutral-800': alert.type === 'negotiation',
                                }"
                            >
                                <div class="font-bold flex items-center gap-1.5 mb-1">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="alert.type === 'risk' ? 'bg-rose-600' : 'bg-amber-500'"></span>
                                    <span>{{ alert.title }}</span>
                                </div>
                                <p class="text-[11px] text-neutral-600">{{ alert.message }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products & Recent Transactions (Tabbed) - Col 7 -->
                <div class="lg:col-span-7 bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                    <div>
                        <!-- Header & Tab Switcher -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2 bg-[#f4f5ee] p-1 rounded-2xl">
                                <button
                                    type="button"
                                    @click="activeBottomTab = 'topProducts'"
                                    :class="activeBottomTab === 'topProducts' ? 'bg-white text-[#0e0f0c] shadow-sm font-bold' : 'text-neutral-500 font-semibold'"
                                    class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer"
                                >
                                    5 Menu Terlaris
                                </button>
                                <button
                                    type="button"
                                    @click="activeBottomTab = 'recentTx'"
                                    :class="activeBottomTab === 'recentTx' ? 'bg-white text-[#0e0f0c] shadow-sm font-bold' : 'text-neutral-500 font-semibold'"
                                    class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer"
                                >
                                    Transaksi Terkini
                                </button>
                            </div>

                            <button
                                type="button"
                                @click="showLedgerModal = true"
                                class="text-xs font-bold text-[#0e0f0c] hover:underline cursor-pointer"
                            >
                                Lihat Semua →
                            </button>
                        </div>

                        <!-- Tab 1: Top 5 Products -->
                        <div v-if="activeBottomTab === 'topProducts'" class="space-y-2.5">
                            <div
                                v-for="(prod, i) in props.topProducts"
                                :key="i"
                                class="flex items-center justify-between p-2.5 rounded-xl bg-[#fafbf7] border border-[#e5e9db]"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full bg-[#0e0f0c] text-white font-black text-[11px] flex items-center justify-center">
                                        {{ i + 1 }}
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-[#0e0f0c]">{{ prod.name }}</h4>
                                        <span class="text-[10px] text-neutral-400 font-medium">{{ prod.category }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-black text-[#0e0f0c] tabular-nums">{{ formatRupiah(prod.sales) }}</div>
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full inline-block">
                                        {{ prod.quantity }} unit terjual
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Recent Transactions -->
                        <div v-else class="space-y-2.5">
                            <div
                                v-for="tx in props.recentTransactions.slice(0, 5)"
                                :key="tx.id"
                                class="flex items-center justify-between p-2.5 rounded-xl bg-[#fafbf7] border border-[#e5e9db]"
                            >
                                <div class="min-w-0 flex-1 mr-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-[11px] font-bold text-[#0e0f0c]">{{ tx.invoice_number }}</span>
                                        <span class="text-[10px] text-neutral-400">{{ tx.sales_person }}</span>
                                    </div>
                                    <div class="text-[11px] text-neutral-600 truncate mt-0.5" :title="tx.items">{{ tx.items }}</div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-xs font-black text-[#0e0f0c] tabular-nums">+ {{ formatRupiah(tx.net_amount) }}</div>
                                    <span class="text-[10px] text-neutral-400">{{ tx.date }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Transaction Ledger Modal -->
        <TransactionLedgerModal
            :show="showLedgerModal"
            :transactions="props.allTransactions && props.allTransactions.length > 0 ? props.allTransactions : props.recentTransactions"
            @close="showLedgerModal = false"
        />
    </div>
</template>
