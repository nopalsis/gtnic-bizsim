<script setup>
import { ref, computed, watch } from 'vue';
import PaginationNav from '@/Components/PaginationNav.vue';
import { useChartSelection } from '@/Composables/useChartSelection';

const props = defineProps({
    workshopName: String,
    kpis: Object,
    capacity: Object,
    categorySales: Array,
    costDistribution: Array,
    recentTransactions: Array,
    topProducts: Array,
    trendHistory: Array,
    healthScore: Object,
    alerts: Array,
    quickSimulatorBaseline: Object,
});

const emit = defineEmits(['openLedger']);

const trendPeriod = ref('7d'); // '7d' (7 Hari Terakhir), 'weekly' (Per 7 Hari / Mingguan), 'all_months' (Semua - Per Bulan)
const {
    selectedItem,
    activeItem,
    isActive,
    onPointerEnter,
    onPointerLeave,
    toggleSelect,
    clearSelection,
} = useChartSelection((item) => item?.key ?? item?.date);

watch(trendPeriod, clearSelection);

// Pagination Riwayat Transaksi Penjualan Dashboard
const txCurrentPage = ref(1);
const txPerPage = ref(5);

const txTotalPages = computed(() => {
    const list = props.recentTransactions || [];
    return Math.max(1, Math.ceil(list.length / txPerPage.value));
});

watch(txTotalPages, (max) => {
    if (txCurrentPage.value > max) txCurrentPage.value = max;
});

const paginatedRecentTransactions = computed(() => {
    const list = props.recentTransactions || [];
    const start = (txCurrentPage.value - 1) * txPerPage.value;
    return list.slice(start, start + txPerPage.value);
});

const goToTxPage = (page) => {
    if (page >= 1 && page <= txTotalPages.value) {
        txCurrentPage.value = page;
    }
};

const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};

// Data Grafik Batang Adaptif (Harian, Per 7 Hari/Mingguan, atau Bulanan untuk Semua)
const activeChartData = computed(() => {
    if (!props.trendHistory || props.trendHistory.length === 0) return [];

    let rawList = [];

    if (trendPeriod.value === 'all_months') {
        // Parameter BULANAN untuk Semua Penjualan (menghindari bug visual batang berdesakan)
        const monthsMap = {};
        props.trendHistory.forEach((item) => {
            const ym = item.full_date ? item.full_date.substring(0, 7) : '2026-11';
            if (!monthsMap[ym]) {
                const parts = ym.split('-');
                const year = parts[0] || '2026';
                const monthNum = parseInt(parts[1] || '1', 10);
                const shortNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                const longNames = [
                    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ];
                monthsMap[ym] = {
                    key: ym,
                    date: `${shortNames[monthNum - 1]} ${year}`,
                    subLabel: 'Bulanan',
                    full_date: `Bulan ${longNames[monthNum - 1]} ${year}`,
                    amount: 0,
                    count: 0,
                };
            }
            monthsMap[ym].amount += item.amount;
            monthsMap[ym].count += item.count;
        });
        rawList = Object.values(monthsMap);
    } else if (trendPeriod.value === 'weekly') {
        // Parameter PER 7 HARI (Mingguan)
        const chunkSize = 7;
        const recentHistory = props.trendHistory.slice(-28);
        for (let i = 0; i < recentHistory.length; i += chunkSize) {
            const chunk = recentHistory.slice(i, i + chunkSize);
            const startStr = chunk[0].date;
            const endStr = chunk[chunk.length - 1].date;
            const weekNum = Math.floor(i / chunkSize) + 1;
            rawList.push({
                key: `mgg-${weekNum}`,
                date: `Mgg ${weekNum}`,
                subLabel: `${startStr} - ${endStr}`,
                full_date: `Minggu ke-${weekNum} (${startStr} s/d ${endStr})`,
                amount: chunk.reduce((sum, c) => sum + c.amount, 0),
                count: chunk.reduce((sum, c) => sum + c.count, 0),
            });
        }
    } else {
        // Parameter HARIAN (7 Hari Transaksi Terakhir)
        rawList = props.trendHistory.slice(-7).map((item) => ({
            ...item,
            key: item.full_date || item.date,
            subLabel: 'Harian',
        }));
    }

    if (rawList.length === 0) return [];

    const maxAmount = Math.max(...rawList.map((t) => t.amount), 500000);
    const maxCount = Math.max(...rawList.map((t) => t.count), 5);

    return rawList.map((item) => {
        const amountRatio = item.amount / maxAmount;
        const countRatio = item.count / maxCount;

        return {
            ...item,
            // Batas maksimal 82% agar ujung atas tidak membentur atap grafik
            amountHeightPct: Math.max(8, Math.min(82, Math.round(amountRatio * 82))),
            countHeightPct: Math.max(8, Math.min(82, Math.round(countRatio * 82))),
        };
    });
});

const barWidthClass = computed(() => {
    if (trendPeriod.value === 'all_months') return 'w-6 sm:w-10';
    if (trendPeriod.value === 'weekly') return 'w-5 sm:w-8';
    return 'w-3.5 sm:w-5';
});

const maxTrendAmount = computed(() => {
    if (!activeChartData.value || activeChartData.value.length === 0) return 0;
    return Math.max(...activeChartData.value.map((t) => t.amount), 0);
});

const maxTrendCount = computed(() => {
    if (!activeChartData.value || activeChartData.value.length === 0) return 0;
    return Math.max(...activeChartData.value.map((t) => t.count), 0);
});
</script>

<template>
    <div class="flex-1 flex flex-col gap-5 min-w-0 max-w-full">
        <!-- Header Utama -->
        <header class="bg-white/90 backdrop-blur-md rounded-2xl sm:rounded-[24px] p-3.5 sm:p-5 border border-[#dce0cd] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 shadow-sm">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#c6ef4e] border border-[#a2c833] shrink-0"></span>
                    <h1 class="text-lg sm:text-xl font-black text-[#0e0f0c] tracking-tight truncate">{{ props.workshopName || 'OptimaBiz Leathercraft' }}</h1>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#0e0f0c] text-[#c6ef4e] border border-[#c6ef4e]/40 shadow-xs shrink-0">
                        Super Admin
                    </span>
                </div>
                <p class="text-xs text-[#737868] font-medium mt-1 leading-snug">
                    Ringkasan penjualan, transaksi, produk terlaris, dan grafik harian bengkel
                </p>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto justify-between sm:justify-end">
                <!-- Status Kondisi Usaha -->
                <div class="flex items-center gap-2 bg-[#f4f5ee] px-2.5 sm:px-3.5 py-1.5 rounded-xl sm:rounded-2xl border border-[#e1e5d3]">
                    <div class="text-right">
                        <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-[#737868] leading-tight">Kondisi Usaha</div>
                        <div class="text-[11px] sm:text-xs font-black text-[#0e0f0c] leading-tight">
                            {{ props.healthScore?.grade || 'A-' }} · {{ props.healthScore?.score || 88 }} Poin
                        </div>
                    </div>
                    <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-[#0e0f0c] text-[#c6ef4e] font-black text-xs flex items-center justify-center shrink-0">
                        {{ props.healthScore?.grade?.charAt(0) || 'A' }}
                    </div>
                </div>

                <!-- Tombol Buku Besar -->
                <button
                    type="button"
                    @click="emit('openLedger')"
                    class="bg-[#0e0f0c] hover:bg-neutral-800 text-white text-xs font-bold px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl sm:rounded-2xl transition flex items-center gap-1.5 shadow-sm cursor-pointer whitespace-nowrap shrink-0"
                >
                    <span class="material-symbols-outlined text-[16px] text-[#c6ef4e]">menu_book</span>
                    <span>Buku Penjualan</span>
                </button>
            </div>
        </header>

        <!-- 4 Kartu Ringkasan Utama: 1. Total Penjualan (Omset), 2. Jumlah Transaksi, 3. Produk Terjual, 4. Rata-rata Omset per Hari -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4" aria-label="Ringkasan Utama Dashboard">
            <!-- 1. Total Penjualan (Omset) -->
            <div class="bg-white rounded-2xl sm:rounded-[22px] p-3.5 sm:p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between hover:border-[#c6ef4e] transition-all">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#737868] truncate">Total Penjualan</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                        Bulan Ini
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                    {{ formatRupiah(props.kpis?.netRevenue) }}
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1 truncate">
                    Sebelum diskon: {{ formatRupiah(props.kpis?.grossRevenue) }}
                </div>
            </div>

            <!-- 2. Jumlah Transaksi -->
            <div class="bg-white rounded-2xl sm:rounded-[22px] p-3.5 sm:p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between hover:border-[#c6ef4e] transition-all">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#737868] truncate">Jumlah Transaksi</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#f4f5ee] text-[#0e0f0c] border border-[#e1e5d3] shrink-0">
                        Nota Selesai
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                    {{ props.kpis?.totalTransactions || 0 }} Transaksi
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1 truncate">
                    Total nota diproses
                </div>
            </div>

            <!-- 3. Produk Terjual -->
            <div class="bg-white rounded-2xl sm:rounded-[22px] p-3.5 sm:p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between hover:border-[#c6ef4e] transition-all">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#737868] truncate">Produk Terjual</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#c6ef4e]/30 text-[#0e0f0c] border border-[#a2c833] shrink-0">
                        Beban {{ props.capacity?.usagePercent || 0 }}%
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                    {{ props.kpis?.totalProductsSold || props.capacity?.soldUnits || 0 }} Pasang
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1 truncate">
                    Batas: {{ props.capacity?.maxCapacity || 500 }} pasang/bln
                </div>
            </div>

            <!-- 4. Rata-rata Omset per Hari -->
            <div class="bg-white rounded-2xl sm:rounded-[22px] p-3.5 sm:p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between hover:border-[#c6ef4e] transition-all">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#737868] truncate">Rata-rata per Hari</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0">
                        Harian
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                    {{ formatRupiah(props.kpis?.dailyAvgRevenue) }}
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1 truncate">
                    Dari {{ props.kpis?.activeDaysCount || 1 }} hari aktif
                </div>
            </div>
        </section>

        <!-- Baris 2: 5. Grafik Penjualan (Col 7) & 6. Top Produk Terlaris (Col 5) -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
            <!-- 5. Grafik Penjualan (Grafik Batang: Total Omset & Jumlah Transaksi Harian / Per 7 Hari / Bulanan) -->
            <div class="lg:col-span-7 bg-white rounded-2xl sm:rounded-[24px] p-3.5 sm:p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div>
                    <!-- Header Grafik & Filter Periode -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#c6ef4e]"></span>
                                <h2 class="text-sm sm:text-base font-black text-[#0e0f0c]">Grafik Penjualan</h2>
                            </div>
                            <p class="text-[11px] text-[#737868] font-medium mt-0.5">
                                {{
                                    trendPeriod === 'all_months'
                                        ? 'Perbandingan omset dan transaksi per bulan'
                                        : trendPeriod === 'weekly'
                                        ? 'Perbandingan omset dan transaksi per 7 hari'
                                        : 'Perbandingan omset dan transaksi per hari (7 hari terakhir)'
                                }}
                            </p>
                        </div>

                        <!-- Filter Pilihan Rentang (Harian, Per 7 Hari, Semua/Per Bulan) -->
                        <div class="flex items-center gap-1 bg-[#f4f5ee] p-1 rounded-xl border border-[#e1e5d3] self-stretch sm:self-auto justify-between sm:justify-start">
                            <button
                                type="button"
                                @click="trendPeriod = '7d'"
                                :class="[
                                    'flex-1 sm:flex-initial px-2 sm:px-2.5 py-1 text-[10px] font-bold rounded-lg transition-all cursor-pointer text-center',
                                    trendPeriod === '7d'
                                        ? 'bg-[#0e0f0c] text-[#c6ef4e] shadow-xs'
                                        : 'text-[#737868] hover:text-[#0e0f0c]'
                                ]"
                            >
                                7 Hari
                            </button>
                            <button
                                type="button"
                                @click="trendPeriod = 'weekly'"
                                :class="[
                                    'flex-1 sm:flex-initial px-2 sm:px-2.5 py-1 text-[10px] font-bold rounded-lg transition-all cursor-pointer text-center',
                                    trendPeriod === 'weekly'
                                        ? 'bg-[#0e0f0c] text-[#c6ef4e] shadow-xs'
                                        : 'text-[#737868] hover:text-[#0e0f0c]'
                                ]"
                            >
                                Per 7 Hari
                            </button>
                            <button
                                type="button"
                                @click="trendPeriod = 'all_months'"
                                :class="[
                                    'flex-1 sm:flex-initial px-2 sm:px-2.5 py-1 text-[10px] font-bold rounded-lg transition-all cursor-pointer flex items-center justify-center gap-1 text-center',
                                    trendPeriod === 'all_months'
                                        ? 'bg-[#0e0f0c] text-[#c6ef4e] shadow-xs'
                                        : 'text-[#737868] hover:text-[#0e0f0c]'
                                ]"
                            >
                                <span>Semua</span>
                                <span class="text-[9px] opacity-75 hidden sm:inline">(Per Bulan)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Strip Rincian Interaktif (Tidak akan pernah terpotong, support tap mobile & hover desktop) -->
                    <div
                        v-if="activeItem"
                        class="mb-3 px-3.5 py-2 rounded-xl bg-[#0e0f0c] text-white flex items-center justify-between gap-2 text-xs shadow-md border border-neutral-800 transition-all"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 sm:gap-4 flex-1 min-w-0">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2 h-2 rounded-full bg-[#c6ef4e] animate-pulse shrink-0"></span>
                                <span class="font-bold text-[#c6ef4e] truncate">{{ activeItem.full_date || activeItem.date }}</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] font-semibold">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-white shrink-0"></span>
                                    Omset: <strong class="text-white font-black">{{ formatRupiah(activeItem.amount) }}</strong>
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-[#c6ef4e] shrink-0"></span>
                                    Transaksi: <strong class="text-[#c6ef4e] font-black">{{ activeItem.count }} Nota</strong>
                                </span>
                            </div>
                        </div>

                        <button
                            v-if="selectedItem"
                            type="button"
                            @click="clearSelection"
                            class="w-5 h-5 rounded-full bg-neutral-800 hover:bg-neutral-700 text-neutral-300 hover:text-white text-[10px] font-bold flex items-center justify-center shrink-0 cursor-pointer"
                            title="Tutup rincian"
                            aria-label="Tutup rincian"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Strip Status Standar saat tidak aktif -->
                    <div
                        v-else
                        class="mb-3 px-3.5 py-2 rounded-xl bg-[#fafbf7] border border-[#e5e9db] text-[#737868] flex items-center justify-between text-xs"
                    >
                        <div class="flex items-center gap-2 text-[11px]">
                            <span class="material-symbols-outlined text-[15px] text-[#0e0f0c]">touch_app</span>
                            <span>Ketuk batang grafik untuk melihat angka detail</span>
                        </div>
                        <div class="text-[11px] font-bold text-[#0e0f0c] hidden sm:block">
                            Puncak {{ trendPeriod === 'all_months' ? 'Bulanan' : (trendPeriod === 'weekly' ? 'Mingguan' : 'Harian') }}:
                            {{ formatRupiah(maxTrendAmount) }} ({{ maxTrendCount }} Nota)
                        </div>
                    </div>

                    <!-- Area Grafik Batang Ganda -->
                    <div class="relative w-full h-56 bg-[#fdfdfc] rounded-2xl border border-[#ecefe4] p-3 flex flex-col justify-between">
                        <!-- Garis Bantu Horizontal di Background -->
                        <div class="absolute inset-x-3 inset-y-3 flex flex-col justify-between pointer-events-none opacity-40">
                            <div class="border-b border-dashed border-[#dce0cd] w-full"></div>
                            <div class="border-b border-dashed border-[#dce0cd] w-full"></div>
                            <div class="border-b border-[#dce0cd] w-full"></div>
                        </div>

                        <!-- Kontainer Batang (Responsif, Berjarak Rapi, Bebas Bug Visual) -->
                        <div
                            v-if="activeChartData.length > 0"
                            class="relative z-10 w-full h-full flex items-end justify-around gap-1 sm:gap-3 pt-2 pb-1"
                        >
                            <div
                                v-for="(item, idx) in activeChartData"
                                :key="'chart-item-' + idx"
                                role="button"
                                tabindex="0"
                                :aria-pressed="isActive(item)"
                                :aria-label="`Detail ${item.full_date || item.date}`"
                                @click="toggleSelect(item)"
                                @keydown.enter.prevent="toggleSelect(item)"
                                @keydown.space.prevent="toggleSelect(item)"
                                @pointerenter="onPointerEnter($event, item)"
                                @pointerleave="onPointerLeave($event)"
                                :class="[
                                    'flex-1 max-w-[120px] h-full flex flex-col items-center justify-end group cursor-pointer rounded-xl py-1 transition-colors select-none touch-manipulation outline-none',
                                    isActive(item)
                                        ? 'bg-neutral-100/90 ring-1 ring-[#a2c833]'
                                        : 'hover:bg-neutral-100/70 focus-visible:ring-1 focus-visible:ring-[#0e0f0c]'
                                ]"
                            >
                                <!-- 2 Batang Berdampingan: Omset & Transaksi -->
                                <div class="w-full h-40 flex items-end justify-center gap-1 sm:gap-1.5 pb-1">
                                    <!-- Batang 1: Total Omset (Hitam) -->
                                    <div
                                        :class="[
                                            'bg-[#0e0f0c] group-hover:bg-neutral-800 rounded-t-sm shadow-xs transition-all duration-300',
                                            barWidthClass
                                        ]"
                                        :style="{ height: `${item.amountHeightPct}%` }"
                                        :title="`Omset: ${formatRupiah(item.amount)}`"
                                    ></div>

                                    <!-- Batang 2: Berapa Transaksi (Hijau Lime) -->
                                    <div
                                        :class="[
                                            'bg-[#c6ef4e] group-hover:bg-[#b5e038] border border-[#a2c833] rounded-t-sm shadow-xs transition-all duration-300',
                                            barWidthClass
                                        ]"
                                        :style="{ height: `${item.countHeightPct}%` }"
                                        :title="`Transaksi: ${item.count} nota`"
                                    ></div>
                                </div>

                                <!-- Label Keterangan di Bawah Batang -->
                                <div class="w-full text-center mt-1">
                                    <div
                                        :class="[
                                            'transition-colors truncate px-0.5',
                                            isActive(item)
                                                ? 'font-black text-[#0e0f0c]'
                                                : 'font-bold text-[#737868] group-hover:text-[#0e0f0c] group-hover:font-black',
                                            trendPeriod === 'all_months' ? 'text-[11px] sm:text-xs' : 'text-[10px] sm:text-[11px]'
                                        ]"
                                    >
                                        {{ item.date }}
                                    </div>
                                    <div v-if="item.subLabel && trendPeriod === 'weekly'" class="text-[9px] text-[#737868]/80 truncate">
                                        {{ item.subLabel }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- State Kosong -->
                        <div v-else class="h-full flex items-center justify-center text-xs text-[#737868] font-medium">
                            Belum ada riwayat transaksi penjualan.
                        </div>
                    </div>
                </div>

                <!-- Footer Keterangan Legenda & Puncak -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between text-[11px] text-[#737868] pt-3 border-t border-neutral-100 mt-3 gap-2">
                    <!-- Legenda 2 Batang -->
                    <div class="flex flex-wrap items-center gap-2 sm:gap-4">
                        <div class="flex items-center gap-1.5 font-bold text-[#0e0f0c]">
                            <span class="w-2.5 h-2.5 rounded bg-[#0e0f0c] shrink-0"></span>
                            <span>Hitam: Omset</span>
                        </div>
                        <div class="flex items-center gap-1.5 font-bold text-[#0e0f0c]">
                            <span class="w-2.5 h-2.5 rounded bg-[#c6ef4e] border border-[#a2c833] shrink-0"></span>
                            <span>Hijau: Transaksi</span>
                        </div>
                    </div>

                    <span class="text-[#0e0f0c] font-bold">
                        Beban Kapasitas: {{ props.capacity?.soldUnits || 0 }} / {{ props.capacity?.maxCapacity || 500 }} Pasang
                    </span>
                </div>
            </div>

            <!-- 6. Top Produk Terlaris -->
            <div class="lg:col-span-5 bg-white rounded-2xl sm:rounded-[24px] p-3.5 sm:p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#c6ef4e]"></span>
                                <h2 class="text-sm sm:text-base font-black text-[#0e0f0c]">Top Produk Terlaris</h2>
                            </div>
                            <p class="text-[11px] text-[#737868] font-medium mt-0.5">Model sepatu paling diminati</p>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#c6ef4e]/30 text-[#0e0f0c] border border-[#a2c833] shrink-0">
                            Paling Laku
                        </span>
                    </div>

                    <!-- List 5 Top Produk -->
                    <div class="space-y-2 mt-2">
                        <div
                            v-for="(prod, idx) in (props.topProducts || []).slice(0, 5)"
                            :key="prod.sku || idx"
                            class="flex items-center justify-between p-2.5 sm:p-3 rounded-xl bg-[#fafbf7] border border-[#e5e9db] hover:border-[#c6ef4e] transition-colors"
                        >
                            <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                <div
                                    :class="[
                                        'w-6 h-6 sm:w-7 sm:h-7 rounded-lg flex items-center justify-center font-black text-[11px] sm:text-xs shrink-0',
                                        idx === 0
                                            ? 'bg-[#c6ef4e] text-[#0e0f0c] shadow-xs'
                                            : idx === 1
                                            ? 'bg-[#0e0f0c] text-white'
                                            : 'bg-[#eeeee7] text-[#737868]'
                                    ]"
                                >
                                    #{{ idx + 1 }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-[#0e0f0c] truncate" :title="prod.name">
                                        {{ prod.name }}
                                    </p>
                                    <span class="text-[10px] text-[#737868]">
                                        {{ prod.category }}
                                    </span>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="text-xs font-black text-[#0e0f0c] block">
                                    {{ prod.quantity }} {{ prod.unit || 'psg' }}
                                </span>
                                <span class="text-[10px] text-[#506600] font-semibold">
                                    {{ formatRupiah(prod.sales) }}
                                </span>
                            </div>
                        </div>

                        <div v-if="!props.topProducts || props.topProducts.length === 0" class="py-6 text-center text-[#737868]">
                            <p class="text-xs font-medium">Belum ada data penjualan produk.</p>
                        </div>
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-neutral-100 flex items-center justify-between text-[11px] text-[#737868]">
                    <span>Total model: {{ props.topProducts?.length || 0 }} produk</span>
                    <a href="/sales" class="text-xs font-bold text-[#0e0f0c] hover:underline cursor-pointer">Lihat Penjualan →</a>
                </div>
            </div>
        </section>

        <!-- Baris 3: 7. Riwayat Transaksi Terbaru (Full Width) -->
        <section class="w-full">
            <!-- 7. Riwayat Transaksi Terbaru -->
            <div class="bg-white rounded-2xl sm:rounded-[24px] p-3.5 sm:p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#c6ef4e]"></span>
                                <h2 class="text-sm sm:text-base font-black text-[#0e0f0c]">Riwayat Transaksi Terbaru</h2>
                            </div>
                            <p class="text-[11px] text-[#737868] font-medium mt-0.5">Catatan pesanan dan faktur yang terakhir diproses</p>
                        </div>
                        <button
                            type="button"
                            @click="emit('openLedger')"
                            class="text-xs font-bold text-[#0e0f0c] hover:underline cursor-pointer inline-flex items-center gap-1 shrink-0"
                        >
                            <span>Buku Penjualan</span>
                            <span>→</span>
                        </button>
                    </div>

                    <!-- Daftar Pesanan (Paginated 5 per halaman) -->
                    <div class="space-y-2">
                        <div
                            v-for="tx in paginatedRecentTransactions"
                            :key="tx.id"
                            class="flex items-center justify-between p-2.5 sm:p-3.5 rounded-xl bg-[#fafbf7] border border-[#e5e9db]"
                        >
                            <div class="min-w-0 flex-1 mr-3">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-black text-[#0e0f0c]">{{ tx.invoice_number }}</span>
                                    <span class="text-xs font-bold text-neutral-800">· {{ tx.customer_name }}</span>
                                    <span class="text-[10px] text-neutral-400">· {{ tx.date }}</span>
                                </div>
                                <div class="text-[11px] text-[#737868] truncate mt-0.5" :title="tx.items">
                                    {{ tx.items }}
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="text-xs sm:text-sm font-black text-[#0e0f0c]">
                                    {{ formatRupiah(tx.net_amount) }}
                                </div>
                                <span
                                    :class="[
                                        'text-[9px] sm:text-[10px] font-bold px-2 py-0.5 rounded-full inline-block mt-0.5',
                                        tx.discount_percent <= 10.0
                                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                            : 'bg-amber-50 text-amber-700 border border-amber-200'
                                    ]"
                                >
                                    {{ tx.discount_percent > 0 ? `-${tx.discount_percent}%` : 'Pas' }}
                                </span>
                            </div>
                        </div>

                        <div v-if="!props.recentTransactions || props.recentTransactions.length === 0" class="py-8 text-center text-[#737868]">
                            <p class="text-xs font-medium">Belum ada rekaman transaksi penjualan.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Pagination & Tautan Buku Penjualan -->
                <div class="pt-3 mt-3 border-t border-neutral-100 flex flex-col sm:flex-row items-center justify-between text-[11px] text-[#737868] gap-2.5">
                    <div class="flex items-center gap-2">
                        <span>Halaman {{ txCurrentPage }} dari {{ txTotalPages }}</span>
                        <span class="text-neutral-300">·</span>
                        <button
                            type="button"
                            @click="emit('openLedger')"
                            class="font-bold text-[#0e0f0c] hover:underline cursor-pointer"
                        >
                            Buku Catatan Lengkap
                        </button>
                    </div>

                    <!-- Tombol Navigasi Pagination Halaman -->
                    <PaginationNav
                        v-if="txTotalPages > 1"
                        variant="compact"
                        prev-label="Prev"
                        next-label="Next"
                        :current-page="txCurrentPage"
                        :total-pages="txTotalPages"
                        @change="goToTxPage"
                    />
                </div>
            </div>
        </section>
    </div>
</template>

