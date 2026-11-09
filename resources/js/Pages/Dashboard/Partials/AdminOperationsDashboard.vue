<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    workshopName: String,
    adminOperationsData: {
        type: Object,
        default: () => ({
            totalTransactionsInput: 0,
            totalItemsSoldQty: 0,
            activeProductsCount: 0,
            lowStockCount: 0,
            dailyInputTrend: [],
            lowStockProducts: [],
            recentInputActivities: [],
        }),
    },
});

// Bar heights for daily input volume trend (Count of transactions)
const inputBars = computed(() => {
    const list = props.adminOperationsData?.dailyInputTrend || [];
    if (list.length === 0) return [];

    const counts = list.map((t) => t.count);
    const maxVal = Math.max(...counts, 5);

    return list.map((item) => {
        const heightPct = Math.round((item.count / maxVal) * 100);
        return {
            ...item,
            heightPct: Math.max(12, Math.min(100, heightPct)),
        };
    });
});
</script>

<template>
    <div class="flex-1 flex flex-col gap-5 min-w-0 max-w-full font-sans">
        <!-- Header Section -->
        <header class="bg-white/90 backdrop-blur-md rounded-2xl sm:rounded-[24px] p-3.5 sm:p-5 border border-[#dce0cd] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 shadow-sm">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#c6ef4e] border border-[#a2c833] shrink-0"></span>
                    <h1 class="text-lg sm:text-xl font-black text-[#0e0f0c] tracking-tight truncate">
                        {{ props.workshopName || 'OptimaBiz Leathercraft' }}
                    </h1>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#0e0f0c] text-[#c6ef4e] border border-[#c6ef4e]/30 shrink-0">
                        Admin Operasional
                    </span>
                </div>
                <p class="text-xs text-[#737868] font-medium mt-1 leading-snug">
                    Catat nota penjualan baru, cek sisa stok sepatu, dan periksa barang masuk
                </p>
            </div>

            <!-- Tombol Pintas Input Cepat -->
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <Link
                    href="/sales"
                    class="flex-1 sm:flex-none bg-[#0e0f0c] hover:bg-[#20221c] text-white text-xs font-bold px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl sm:rounded-2xl transition flex items-center justify-center gap-1.5 shadow-sm group cursor-pointer whitespace-nowrap"
                >
                    <span class="material-symbols-outlined text-[16px] text-[#c6ef4e] group-hover:scale-110 transition-transform">
                        post_add
                    </span>
                    <span>+ Catat Penjualan</span>
                </Link>

                <Link
                    href="/inventory"
                    class="flex-1 sm:flex-none bg-[#f4f4ed] hover:bg-[#e1e5d3] text-[#0e0f0c] text-xs font-bold px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl sm:rounded-2xl border border-[#dce0cd] transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer whitespace-nowrap"
                >
                    <span class="material-symbols-outlined text-[16px]">add_box</span>
                    <span>Cek Stok Barang</span>
                </Link>
            </div>
        </header>

        <!-- 4 Metrik Utama Operasional (Non-Finansial) -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4" aria-label="Metrik Operasional Admin">
            <!-- 1. Total Transaksi Terinput Bulan Ini -->
            <div class="bg-white rounded-2xl sm:rounded-[22px] p-3.5 sm:p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#737868] truncate">
                        Penjualan Dicatat
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                        Bulan Ini
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-[#0e0f0c] mt-2">
                    {{ props.adminOperationsData?.totalTransactionsInput || 0 }} Nota
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1 truncate">
                    Total nota penjualan masuk
                </div>
            </div>

            <!-- 2. Total Item Terjual / Keluar -->
            <div class="bg-white rounded-2xl sm:rounded-[22px] p-3.5 sm:p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#737868] truncate">
                        Sepatu Terjual
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#c6ef4e]/30 text-[#0e0f0c] border border-[#a2c833] shrink-0">
                        Barang Keluar
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-[#0e0f0c] mt-2">
                    {{ props.adminOperationsData?.totalItemsSoldQty || 0 }} Pasang
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1 truncate">
                    Jumlah pasang keluar dari bengkel
                </div>
            </div>

            <!-- 3. Total Produk Aktif di Katalog -->
            <div class="bg-white rounded-2xl sm:rounded-[22px] p-3.5 sm:p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#737868] truncate">
                        Model Siap Jual
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-stone-100 text-[#0e0f0c] shrink-0">
                        Katalog
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-[#0e0f0c] mt-2">
                    {{ props.adminOperationsData?.activeProductsCount || 0 }} Model
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1 truncate">
                    Model sepatu, sandal & kulit
                </div>
            </div>

            <!-- 4. Peringatan Stok Kritis / Menipis -->
            <div class="bg-white rounded-2xl sm:rounded-[22px] p-3.5 sm:p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#737868] truncate">
                        Stok Menipis
                    </span>
                    <span
                        :class="[
                            'text-[10px] font-bold px-2 py-0.5 rounded-full border shrink-0',
                            (props.adminOperationsData?.lowStockCount || 0) > 0
                                ? 'bg-rose-50 text-rose-700 border-rose-200'
                                : 'bg-emerald-50 text-emerald-700 border-emerald-200'
                        ]"
                    >
                        {{ (props.adminOperationsData?.lowStockCount || 0) > 0 ? 'Perlu Restock' : 'Aman' }}
                    </span>
                </div>
                <div
                    :class="[
                        'text-xl sm:text-2xl font-black mt-2',
                        (props.adminOperationsData?.lowStockCount || 0) > 0 ? 'text-rose-600' : 'text-[#0e0f0c]'
                    ]"
                >
                    {{ props.adminOperationsData?.lowStockCount || 0 }} Produk
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1 truncate">
                    Perlu dibuat atau dibeli lagi
                </div>
            </div>
        </section>

        <!-- Visualisasi: Grafik Volume Input Transaksi & Daftar Stok Menipis -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
            <!-- Grafik Volume Input Transaksi (Kuantitas Nota per Hari) - Col 7 -->
            <div class="lg:col-span-7 bg-white rounded-2xl sm:rounded-[24px] p-3.5 sm:p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-sm sm:text-base font-black text-[#0e0f0c]">Jumlah Penjualan per Hari</h2>
                            <p class="text-[11px] text-[#737868] font-medium">Berapa banyak nota yang dicatat setiap hari</p>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#f4f5ee] text-[#0e0f0c] border border-[#e1e5d3] shrink-0">
                            {{ inputBars.length }} Periode
                        </span>
                    </div>

                    <!-- Bar Chart Visualizer -->
                    <div class="h-44 flex items-end gap-2 pt-6 pb-2 px-2 border-b border-[#f0f1eb]">
                        <div
                            v-for="(bar, idx) in inputBars"
                            :key="idx"
                            class="flex-1 flex flex-col items-center group relative cursor-pointer"
                        >
                            <!-- Tooltip -->
                            <div class="opacity-0 group-hover:opacity-100 absolute -top-8 bg-[#0e0f0c] text-white text-[10px] font-bold px-2.5 py-1 rounded-lg pointer-events-none transition-all whitespace-nowrap z-20 shadow-md">
                                {{ bar.date }}: {{ bar.count }} Transaksi
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

                <div class="flex flex-wrap items-center justify-between text-[11px] text-[#737868] pt-3 gap-1">
                    <span>Sentuh batang untuk melihat detail harian</span>
                    <span class="font-bold text-[#0e0f0c]">
                        Total: {{ props.adminOperationsData?.totalTransactionsInput || 0 }} Transaksi
                    </span>
                </div>
            </div>

            <!-- Daftar Stok Menipis - Col 5 -->
            <div class="lg:col-span-5 bg-white rounded-2xl sm:rounded-[24px] p-3.5 sm:p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-sm sm:text-base font-black text-[#0e0f0c]">Daftar Stok Sedikit</h2>
                            <p class="text-[11px] text-[#737868] font-medium">Beri tahu pengrajin untuk buat model ini</p>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 shrink-0">
                            Perlu Aksi
                        </span>
                    </div>

                    <!-- Products List -->
                    <div class="space-y-2 mt-2">
                        <div
                            v-for="item in props.adminOperationsData?.lowStockProducts"
                            :key="item.id || item.sku"
                            class="flex items-center justify-between p-2.5 rounded-xl bg-[#fafbf7] border border-[#e5e9db]"
                        >
                            <div class="min-w-0 flex-1 mr-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[9px] font-bold text-[#737868]">{{ item.sku }}</span>
                                    <h4 class="text-xs font-bold text-[#0e0f0c] truncate">{{ item.name }}</h4>
                                </div>
                                <span class="text-[10px] text-[#737868]">{{ item.category }}</span>
                            </div>

                            <div class="text-right shrink-0">
                                <span
                                    :class="[
                                        'text-[10px] font-bold px-2 py-0.5 rounded-full inline-block',
                                        item.current_stock <= 0
                                            ? 'bg-rose-100 text-rose-800'
                                            : 'bg-amber-50 text-amber-700 border border-amber-200'
                                    ]"
                                >
                                    Sisa {{ item.current_stock }} {{ item.unit || 'psg' }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="!props.adminOperationsData?.lowStockProducts || props.adminOperationsData.lowStockProducts.length === 0"
                            class="p-4 text-center text-xs text-[#737868]"
                        >
                            Semua stok produk saat ini masih aman.
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-neutral-100 flex items-center justify-between text-[11px]">
                    <span class="text-[#737868]">Periksa barang masuk</span>
                    <Link href="/inventory" class="font-bold text-[#0e0f0c] hover:underline">
                        Buka Stok Barang →
                    </Link>
                </div>
            </div>
        </section>

        <!-- Aksi & Widget: Riwayat Transaksi Terinput Terkini (Non-Finansial) -->
        <section class="bg-white rounded-2xl sm:rounded-[24px] p-3.5 sm:p-5 border border-[#dce0cd] shadow-sm">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div>
                    <h2 class="text-sm sm:text-base font-black text-[#0e0f0c]">5 Penjualan Terakhir yang Dicatat</h2>
                    <p class="text-[11px] text-[#737868] font-medium">Transaksi penjualan yang baru saja disimpan</p>
                </div>
                <Link
                    href="/sales"
                    class="text-xs font-bold text-[#0e0f0c] hover:underline flex items-center gap-1 shrink-0"
                >
                    <span>Semua Penjualan</span>
                    <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                </Link>
            </div>

            <!-- List of Input Activities -->
            <div class="space-y-2">
                <div
                    v-for="tx in props.adminOperationsData?.recentInputActivities"
                    :key="tx.id"
                    class="flex flex-col sm:flex-row sm:items-center justify-between p-2.5 sm:p-3 rounded-xl bg-[#fafbf7] border border-[#e5e9db] gap-2"
                >
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-xs font-black text-[#0e0f0c]">{{ tx.invoice_number }}</span>
                            <span class="text-[11px] font-bold text-[#0e0f0c]">· {{ tx.customer_name }}</span>
                            <span class="text-[10px] text-[#737868]">({{ tx.sales_person }})</span>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-[#f4f4ed] text-[#0e0f0c] border border-[#dce0cd]">
                                {{ tx.payment_method }}
                            </span>
                        </div>
                        <div class="text-[11px] text-[#737868] truncate mt-0.5">
                            {{ tx.items }}
                        </div>
                    </div>

                    <div class="text-left sm:text-right shrink-0 flex sm:flex-col items-center sm:items-end justify-between sm:justify-start gap-1">
                        <span class="text-[9px] sm:text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                            Tercatat Selesai
                        </span>
                        <div class="text-[10px] text-[#737868]">
                            {{ tx.date }}
                        </div>
                    </div>
                </div>

                <div
                    v-if="!props.adminOperationsData?.recentInputActivities || props.adminOperationsData.recentInputActivities.length === 0"
                    class="p-6 text-center text-[#737868] text-xs"
                >
                    Belum ada riwayat penjualan yang dicatat.
                </div>
            </div>
        </section>
    </div>
</template>
