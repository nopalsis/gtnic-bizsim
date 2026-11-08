<script setup>
import { ref, computed } from 'vue';
import QuickSimulatorWidget from './QuickSimulatorWidget.vue';

const props = defineProps({
    workshopName: String,
    kpis: Object,
    capacity: Object,
    categorySales: Array,
    costDistribution: Array,
    recentTransactions: Array,
    trendHistory: Array,
    healthScore: Object,
    alerts: Array,
    quickSimulatorBaseline: Object,
});

const emit = defineEmits(['openLedger']);

const hoveredTrendPoint = ref(null);

const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};

// Bézier Chart Computations
const trendDataPoints = computed(() => {
    if (!props.trendHistory || props.trendHistory.length === 0) return [];

    const items = props.trendHistory;
    const amounts = items.map((t) => t.amount);
    const maxVal = Math.max(...amounts, 800000);
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
    <div class="flex-1 flex flex-col gap-5 min-w-0">
        <!-- Header Utama -->
        <header class="bg-white/90 backdrop-blur-md rounded-[24px] p-5 border border-[#dce0cd] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#c6ef4e] border border-[#a2c833]"></span>
                    <h1 class="text-xl font-black text-[#0e0f0c] tracking-tight">{{ props.workshopName || 'OptimaBiz Leathercraft' }}</h1>
                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-[#0e0f0c] text-[#c6ef4e] border border-[#c6ef4e]/40 shadow-xs">
                        Super Admin · Laporan & Keuangan
                    </span>
                </div>
                <p class="text-xs text-[#737868] font-medium mt-1">
                    Ringkasan uang masuk, keuntungan bersih, bahan terbuang, dan batas produksi bengkel
                </p>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
                <!-- Status Kondisi Usaha -->
                <div class="flex items-center gap-2 bg-[#f4f5ee] px-3.5 py-1.5 rounded-2xl border border-[#e1e5d3]">
                    <div class="text-right">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-[#737868]">Kondisi Usaha</div>
                        <div class="text-xs font-black text-[#0e0f0c]">
                            {{ props.healthScore?.grade || 'A-' }} · {{ props.healthScore?.score || 88 }} Poin
                        </div>
                    </div>
                    <div class="w-7 h-7 rounded-full bg-[#0e0f0c] text-[#c6ef4e] font-black text-xs flex items-center justify-center">
                        {{ props.healthScore?.grade?.charAt(0) || 'A' }}
                    </div>
                </div>

                <!-- Tombol Buku Besar -->
                <button
                    type="button"
                    @click="emit('openLedger')"
                    class="bg-[#0e0f0c] hover:bg-neutral-800 text-white text-xs font-bold px-4 py-2.5 rounded-2xl transition flex items-center gap-1.5 shadow-sm cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[16px] text-[#c6ef4e]">menu_book</span>
                    <span>Buku Penjualan</span>
                </button>
            </div>
        </header>

        <!-- Kotak Saran & Pengingat -->
        <div class="bg-[#0e0f0c] text-white rounded-[24px] p-4 sm:p-5 border border-[#2a2c24] shadow-sm relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-[#c6ef4e] text-[#0e0f0c] flex items-center justify-center shrink-0 font-bold shadow-md">
                    <span class="material-symbols-outlined text-[22px]">lightbulb</span>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-white/10 text-[#c6ef4e]">
                            Saran Hari Ini
                        </span>
                        <span class="text-xs text-[#868b7d]">Bulan Berjalan</span>
                    </div>
                    <h3 class="text-sm font-bold text-white">
                        Pengingat Bahan Rusak & Batas Kerja Bengkel
                    </h3>
                    <p class="text-xs text-[#dce0cd] mt-0.5 max-w-2xl leading-relaxed">
                        Kulit dan bahan yang rusak tercatat senilai <span class="font-bold text-[#c6ef4e]">{{ formatRupiah(props.kpis?.wasteCost) }}</span> ({{ props.kpis?.wasteCount || 0 }} pasang gagal sol/cacat). Beban kerja pengrajin saat ini <span class="font-bold text-white">{{ props.capacity?.usagePercent || 0 }}%</span> dari batas maksimal {{ props.capacity?.maxCapacity || 500 }} pasang/bulan.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 w-full md:w-auto shrink-0">
                <a
                    href="/simulator"
                    class="w-full md:w-auto text-center px-4 py-2 rounded-xl bg-[#c6ef4e] text-[#0e0f0c] font-bold text-xs hover:bg-[#b5e039] transition cursor-pointer"
                >
                    Coba Hitung Tambah Pesanan
                </a>
            </div>
        </div>

        <!-- 4 Kartu Ringkasan Utama -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Ringkasan Finansial">
            <!-- 1. Total Uang Penjualan Bersih -->
            <div class="bg-white rounded-[22px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-[#737868]">Total Penjualan Bersih</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Diskon {{ props.kpis?.discountRate || 0 }}%
                    </span>
                </div>
                <div class="text-2xl font-black text-[#0e0f0c] mt-2">
                    {{ formatRupiah(props.kpis?.netRevenue) }}
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1 truncate">
                    Sebelum diskon: {{ formatRupiah(props.kpis?.grossRevenue) }}
                </div>
            </div>

            <!-- 2. Keuntungan Bersih -->
            <div class="bg-white rounded-[22px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-[#737868]">Keuntungan Bersih</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#c6ef4e]/30 text-[#0e0f0c] border border-[#a2c833]">
                        Laba Bersih
                    </span>
                </div>
                <div class="text-2xl font-black text-[#0e0f0c] mt-2">
                    {{ formatRupiah(props.kpis?.netProfit) }}
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1 truncate">
                    Sisa bersih setelah bayar bahan, gaji & sewa
                </div>
            </div>

            <!-- 3. Persentase Keuntungan -->
            <div class="bg-white rounded-[22px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-[#737868]">Persentase Keuntungan</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-stone-100 text-[#0e0f0c]">
                        Laba / Penjualan
                    </span>
                </div>
                <div class="text-2xl font-black text-[#0e0f0c] mt-2">
                    {{ props.kpis?.profitMargin || 0 }}%
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1 truncate">
                    Untung kotor: {{ formatRupiah(props.kpis?.grossProfit) }}
                </div>
            </div>

            <!-- 4. Nilai Kulit / Bahan Rusak -->
            <div class="bg-white rounded-[22px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-[#737868]">Nilai Bahan Rusak</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200">
                        {{ props.kpis?.wasteCount || 0 }} Pasang Rusak
                    </span>
                </div>
                <div class="text-2xl font-black text-rose-600 mt-2">
                    {{ formatRupiah(props.kpis?.wasteCost) }}
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1 truncate">
                    Modal bahan yang terbuang
                </div>
            </div>
        </section>

        <!-- Grafik Penjualan & Pembagian Pengeluaran -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
            <!-- Grafik Penjualan vs Batas Kemampuan Produksi (Col 7) -->
            <div class="lg:col-span-7 bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="text-base font-black text-[#0e0f0c]">Tren Penjualan & Batas Produksi Bengkel</h2>
                        <p class="text-[11px] text-[#737868] font-medium">
                            Garis penjualan dibanding kemampuan maksimal bengkel ({{ props.capacity?.maxCapacity || 500 }} pasang)
                        </p>
                    </div>
                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-[#f4f5ee] text-[#0e0f0c] border border-[#e1e5d3]">
                        Beban Tukang: {{ props.capacity?.usagePercent || 0 }}%
                    </span>
                </div>

                <!-- Grafik Kurva -->
                <div class="relative w-full h-44 mt-2">
                    <div class="absolute left-0 top-0 bottom-6 flex flex-col justify-between text-[10px] font-bold text-[#737868]">
                        <span>500rb</span>
                        <span>250rb</span>
                        <span>0rb</span>
                    </div>

                    <div class="ml-9 h-full relative">
                        <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 510 140">
                            <!-- Batas Garis Maksimal -->
                            <line stroke="#f87171" stroke-dasharray="4 4" stroke-width="1.5" x1="0" x2="510" y1="25" y2="25"></line>
                            <text x="320" y="20" fill="#dc2626" font-size="9" font-weight="bold">Batas Maksimal: {{ props.capacity?.maxCapacity || 500 }} Pasang</text>

                            <!-- Garis Bantu -->
                            <line stroke="#f1f3eb" stroke-dasharray="3 3" stroke-width="1.2" x1="0" x2="510" y1="75" y2="75"></line>
                            <line stroke="#f1f3eb" stroke-width="1.2" x1="0" x2="510" y1="120" y2="120"></line>

                            <!-- Garis Tren -->
                            <path
                                :d="bezierPathD"
                                fill="none"
                                stroke="#0e0f0c"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                            ></path>

                            <!-- Titik Data -->
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

                        <!-- Tooltip Balon -->
                        <div
                            v-if="hoveredTrendPoint"
                            class="absolute -top-3 bg-[#0e0f0c] text-white text-[11px] font-bold px-3 py-1 rounded-full shadow-xl border border-neutral-700 pointer-events-none whitespace-nowrap z-20 flex items-center gap-1.5 transition-all"
                            :style="{ left: `${hoveredTrendPoint.x}px`, transform: 'translateX(-50%)' }"
                        >
                            <span class="text-[#c6ef4e]">{{ hoveredTrendPoint.date }}:</span>
                            <span>{{ formatRupiah(hoveredTrendPoint.amount) }}</span>
                            <span class="text-neutral-400">({{ hoveredTrendPoint.count }} transaksi)</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-[11px] text-[#737868] pt-2 border-t border-neutral-100 mt-2">
                    <span>Sudah terjual: {{ props.capacity?.soldUnits || 0 }} Pasang</span>
                    <span class="text-rose-600 font-semibold">Garis merah: Batas maksimal produksi tukang</span>
                </div>
            </div>

            <!-- Pembagian Pengeluaran Bengkel (Col 5) -->
            <div class="lg:col-span-5 bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-base font-black text-[#0e0f0c]">Pembagian Pengeluaran Bengkel</h2>
                            <p class="text-[11px] text-[#737868] font-medium">Kemana uang modal dan biaya bengkel dipakai</p>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#f4f5ee] text-[#0e0f0c] border border-[#e1e5d3]">
                            Biaya Usaha
                        </span>
                    </div>

                    <!-- Batang Rincian Biaya -->
                    <div class="space-y-3.5 mt-4">
                        <div v-for="cost in props.costDistribution" :key="cost.category" class="space-y-1">
                            <div class="flex items-center justify-between text-xs font-bold text-[#0e0f0c]">
                                <span>{{ cost.category }}</span>
                                <span>{{ formatRupiah(cost.amount) }} ({{ cost.percentage }}%)</span>
                            </div>
                            <div class="w-full bg-[#f4f5ee] h-2.5 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="{
                                        'bg-[#0e0f0c]': cost.category.includes('Bahan Kulit') || cost.category.includes('HPP'),
                                        'bg-[#506600]': cost.category.includes('Upah') || cost.category.includes('Tukang'),
                                        'bg-[#828876]': cost.category.includes('Sewa'),
                                        'bg-rose-600': cost.category.includes('Rusak') || cost.category.includes('Limbah'),
                                    }"
                                    :style="{ width: `${cost.percentage}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Bawah -->
                <div class="pt-4 mt-4 border-t border-neutral-100 flex items-center justify-between text-[11px] text-[#737868]">
                    <span>Bahan: {{ formatRupiah(props.costDistribution?.[0]?.amount) }}</span>
                    <span>Bahan Rusak: {{ formatRupiah(props.kpis?.wasteCost) }}</span>
                    <span>Gaji & Sewa: {{ formatRupiah(props.kpis?.totalFixedOpex) }}</span>
                </div>
            </div>
        </section>

        <!-- Hitung Cepat & Pesanan Terbaru -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
            <!-- Hitung Cepat Order Grosir (Col 6) -->
            <div class="lg:col-span-6 flex flex-col">
                <QuickSimulatorWidget :baseline="props.quickSimulatorBaseline" />
            </div>

            <!-- 5 Pesanan Terbaru (Col 6) -->
            <div class="lg:col-span-6 bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-base font-black text-[#0e0f0c]">5 Pesanan & Penjualan Terbaru</h2>
                            <p class="text-[11px] text-[#737868] font-medium">Catatan pembelian terakhir beserta potongan harga yang disepakati</p>
                        </div>
                        <button
                            type="button"
                            @click="emit('openLedger')"
                            class="text-xs font-bold text-[#0e0f0c] hover:underline cursor-pointer"
                        >
                            Lihat Semua →
                        </button>
                    </div>

                    <!-- Daftar Pesanan -->
                    <div class="space-y-2.5">
                        <div
                            v-for="tx in props.recentTransactions.slice(0, 5)"
                            :key="tx.id"
                            class="flex items-center justify-between p-3 rounded-xl bg-[#fafbf7] border border-[#e5e9db]"
                        >
                            <div class="min-w-0 flex-1 mr-3">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-black text-[#0e0f0c]">{{ tx.invoice_number }}</span>
                                    <span class="text-[11px] font-bold text-neutral-700">· {{ tx.customer_name }}</span>
                                    <span class="text-[10px] text-[#737868]">({{ tx.sales_person }})</span>
                                </div>
                                <div class="text-[11px] text-[#737868] truncate mt-0.5" :title="tx.items">
                                    {{ tx.items }}
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="text-xs font-black text-[#0e0f0c]">
                                    {{ formatRupiah(tx.net_amount) }}
                                </div>
                                <span
                                    :class="[
                                        'text-[9px] font-bold px-1.5 py-0.5 rounded-full inline-block mt-0.5',
                                        tx.discount_percent <= 10.0
                                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                            : 'bg-amber-50 text-amber-700 border border-amber-200'
                                    ]"
                                >
                                    {{ tx.discount_percent > 0 ? `Diskon -${tx.discount_percent}%` : 'Harga Pas' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-neutral-100 text-center">
                    <span class="text-[10px] text-[#737868]">
                        Warna hijau: Diskon aman (≤ 10%). Warna kuning: Diskon besar, periksa modal bahan.
                    </span>
                </div>
            </div>
        </section>
    </div>
</template>
