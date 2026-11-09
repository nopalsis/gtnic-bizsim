<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    workshopName: String,
    salesData: {
        type: Object,
        default: () => ({
            myNetRevenue: 0,
            myGrossRevenue: 0,
            myAvgDiscount: 0,
            completedTransactions: {
                total: 0,
                retail: 0,
                wholesale: 0,
            },
            salesTrend: [],
            topSellingStock: [],
            recentTransactions: [],
        }),
    },
});

const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};

// SVG Bar Chart for Sales Daily Trend
const trendBars = computed(() => {
    const list = props.salesData?.salesTrend || [];
    if (list.length === 0) return [];

    const amounts = list.map((t) => t.amount);
    const maxVal = Math.max(...amounts, 500000);

    return list.map((item) => {
        const heightPct = Math.round((item.amount / maxVal) * 100);
        return {
            ...item,
            heightPct: Math.max(8, Math.min(100, heightPct)),
        };
    });
});
</script>

<template>
    <div class="flex-1 flex flex-col gap-5 min-w-0 font-sans">
        <!-- Sales Header Section -->
        <header class="bg-white/90 backdrop-blur-md rounded-[24px] p-5 border border-[#dce0cd] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#c6ef4e] border border-[#a2c833]"></span>
                    <h1 class="text-xl font-black text-[#0e0f0c] tracking-tight">
                        {{ props.workshopName || 'OptimaBiz Leathercraft' }}
                    </h1>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#0e0f0c] text-[#c6ef4e]">
                        Halaman Kerja Sales & Kasir
                    </span>
                </div>
                <p class="text-xs text-[#737868] font-medium mt-1">
                    Lihat hasil penjualan Anda, cek sisa stok sepatu, dan catat nota pesanan baru
                </p>
            </div>

            <!-- Tombol Pintas Aksi: + Catat Transaksi Baru -->
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <Link
                    href="/sales"
                    class="w-full sm:w-auto bg-[#0e0f0c] hover:bg-[#20221c] text-white text-xs font-bold px-5 py-2.5 rounded-2xl transition flex items-center justify-center gap-2 shadow-sm group cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[18px] text-[#c6ef4e] group-hover:scale-110 transition-transform">
                        add_circle
                    </span>
                    <span>+ Catat Pesanan Baru</span>
                </Link>
            </div>
        </header>

        <!-- 3 Metrik Utama (Atas) -->
        <section class="grid grid-cols-1 sm:grid-cols-3 gap-4" aria-label="Metrik Utama Sales">
            <!-- 1. Total Omzet Penjualan Saya -->
            <div class="bg-white rounded-[22px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-[#737868]">
                        Hasil Penjualan Saya
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Bulan Berjalan
                    </span>
                </div>
                <div class="text-2xl font-black text-[#0e0f0c] mt-2">
                    {{ formatRupiah(props.salesData?.myNetRevenue) }}
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1">
                    Total pesanan yang Anda layani
                </div>
            </div>

            <!-- 2. Jumlah Transaksi Selesai (ECERAN vs PARTAI) -->
            <div class="bg-white rounded-[22px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-[#737868]">
                        Total Nota Selesai
                    </span>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-[#f4f4ed] text-[#0e0f0c] border border-[#dce0cd]">
                            Eceran: {{ props.salesData?.completedTransactions?.retail || 0 }}
                        </span>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-[#c6ef4e]/30 text-[#0e0f0c] border border-[#a2c833]">
                            Partai: {{ props.salesData?.completedTransactions?.wholesale || 0 }}
                        </span>
                    </div>
                </div>
                <div class="text-2xl font-black text-[#0e0f0c] mt-2">
                    {{ props.salesData?.completedTransactions?.total || 0 }} Nota
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1">
                    Banyaknya nota belanja yang Anda proses
                </div>
            </div>

            <!-- 3. Rata-rata Diskon Nego -->
            <div class="bg-white rounded-[22px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-[#737868]">
                        Rata-rata Diskon Diberikan
                    </span>
                    <span
                        :class="[
                            'text-[10px] font-bold px-2 py-0.5 rounded-full border',
                            (props.salesData?.myAvgDiscount || 0) <= 10.0
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                : 'bg-amber-50 text-amber-700 border-amber-200'
                        ]"
                    >
                        {{ (props.salesData?.myAvgDiscount || 0) <= 10.0 ? 'Diskon Wajar' : 'Diskon Terlalu Besar' }}
                    </span>
                </div>
                <div class="text-2xl font-black text-[#0e0f0c] mt-2">
                    {{ props.salesData?.myAvgDiscount || 0 }}%
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1">
                    Rata-rata potongan harga yang disepakati
                </div>
            </div>
        </section>

        <!-- Visualisasi: Grafik Pencapaian Harian + Status Stok Terlaris -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
            <!-- Grafik Pencapaian Penjualan Harian/Mingguan (Col 7) -->
            <div class="lg:col-span-7 bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-base font-black text-[#0e0f0c]">Grafik Penjualan Saya per Hari</h2>
                            <p class="text-[11px] text-[#737868] font-medium">Catatan uang penjualan yang Anda buat sepanjang bulan ini</p>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-[#f4f5ee] text-[#0e0f0c] border border-[#e1e5d3]">
                            {{ trendBars.length }} Periode
                        </span>
                    </div>

                    <!-- Bar Chart Visualizer -->
                    <div class="h-44 flex items-end gap-2 pt-6 pb-2 px-2 border-b border-[#f0f1eb]">
                        <div
                            v-for="(bar, idx) in trendBars"
                            :key="idx"
                            class="flex-1 flex flex-col items-center group relative cursor-pointer"
                        >
                            <!-- Tooltip on Hover -->
                            <div class="opacity-0 group-hover:opacity-100 absolute -top-10 bg-[#0e0f0c] text-white text-[10px] font-bold px-2.5 py-1 rounded-lg pointer-events-none transition-all whitespace-nowrap z-20 shadow-md">
                                {{ bar.date }}: {{ formatRupiah(bar.amount) }} ({{ bar.count }} nota)
                            </div>
                            <div
                                class="w-full max-w-[36px] bg-[#0e0f0c] group-hover:bg-[#c6ef4e] rounded-t-lg transition-all"
                                :style="{ height: `${bar.heightPct}%` }"
                            ></div>
                            <span class="text-[9px] font-bold text-[#737868] mt-2 truncate w-full text-center">
                                {{ bar.date }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-[11px] text-[#737868] pt-3">
                    <span>Arahkan kursor ke batang untuk melihat jumlah rupiah per hari</span>
                    <span class="font-bold text-[#0e0f0c]">
                        Total: {{ formatRupiah(props.salesData?.myNetRevenue) }}
                    </span>
                </div>
            </div>

            <!-- Status Stok 5 Produk Terlaris (Col 5) -->
            <div class="lg:col-span-5 bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-base font-black text-[#0e0f0c]">Stok 5 Sepatu Paling Laku</h2>
                            <p class="text-[11px] text-[#737868] font-medium">Cek dulu stoknya sebelum menjanjikan ke pembeli</p>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#c6ef4e]/30 text-[#0e0f0c]">
                            Paling Dicari
                        </span>
                    </div>

                    <!-- Products List -->
                    <div class="space-y-2.5 mt-2">
                        <div
                            v-for="item in props.salesData?.topSellingStock"
                            :key="item.id || item.sku"
                            class="flex items-center justify-between p-2.5 rounded-xl bg-[#fafbf7] border border-[#e5e9db]"
                        >
                            <div class="min-w-0 flex-1 mr-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[9px] font-bold text-[#737868]">{{ item.sku }}</span>
                                    <h4 class="text-xs font-bold text-[#0e0f0c] truncate">{{ item.name }}</h4>
                                </div>
                                <span class="text-[10px] text-[#737868]">{{ item.category }} · Terjual {{ item.sold_qty }} {{ item.unit }}</span>
                            </div>

                            <div class="text-right shrink-0">
                                <span
                                    :class="[
                                        'text-[10px] font-bold px-2 py-0.5 rounded-full inline-block',
                                        item.status === 'Ready'
                                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                            : item.status === 'Stok Menipis'
                                            ? 'bg-amber-50 text-amber-700 border border-amber-200'
                                            : 'bg-rose-50 text-rose-700 border border-rose-200'
                                    ]"
                                >
                                    {{ item.status === 'Ready' ? 'Stok Ada' : (item.status === 'Stok Menipis' ? 'Tinggal Sedikit' : 'Stok Kosong') }} ({{ item.current_stock }} {{ item.unit }})
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-neutral-100 flex items-center justify-between text-[11px]">
                    <span class="text-[#737868]">Sepatu yang hampir habis segera beri tahu bengkel</span>
                    <Link href="/inventory" class="font-bold text-[#0e0f0c] hover:underline">
                        Lihat Semua Stok →
                    </Link>
                </div>
            </div>
        </section>

        <!-- Aksi & Widget: Riwayat Transaksi Terbaru Sales Ini -->
        <section class="bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-black text-[#0e0f0c]">5 Pesanan Terakhir yang Saya Layani</h2>
                    <p class="text-[11px] text-[#737868] font-medium">Rincian nota, nama pembeli, dan potongan harga yang Anda buat</p>
                </div>
                <Link
                    href="/sales"
                    class="text-xs font-bold text-[#0e0f0c] hover:underline flex items-center gap-1"
                >
                    Buka Halaman Penjualan
                    <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                </Link>
            </div>

            <div class="space-y-2.5">
                <div
                    v-for="tx in props.salesData?.recentTransactions"
                    :key="tx.id"
                    class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-xl bg-[#fafbf7] border border-[#e5e9db] gap-2"
                >
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-black text-[#0e0f0c]">{{ tx.invoice_number }}</span>
                            <span class="text-[11px] font-bold text-[#0e0f0c]">· {{ tx.customer_name }}</span>
                            <span class="text-[10px] text-[#737868]">Bayar via {{ tx.payment_method }}</span>
                            <span
                                :class="[
                                    'text-[9px] font-bold px-1.5 py-0.5 rounded-full',
                                    tx.discount_percent <= 10
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-amber-50 text-amber-700'
                                ]"
                            >
                                {{ tx.discount_percent > 0 ? `Diskon ${tx.discount_percent}%` : 'Harga Pas' }}
                            </span>
                        </div>
                        <div class="text-[11px] text-[#737868] truncate mt-1">
                            {{ tx.items }}
                        </div>
                    </div>

                    <div class="text-left sm:text-right shrink-0">
                        <div class="text-sm font-black text-[#0e0f0c]">
                            {{ formatRupiah(tx.net_amount) }}
                        </div>
                        <div class="text-[10px] text-[#737868]">
                            {{ tx.date }}
                        </div>
                    </div>
                </div>

                <div
                    v-if="!props.salesData?.recentTransactions || props.salesData.recentTransactions.length === 0"
                    class="p-6 text-center text-[#737868] text-xs"
                >
                    Belum ada riwayat pesanan yang tercatat untuk akun Anda.
                </div>
            </div>
        </section>
    </div>
</template>
