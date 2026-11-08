<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    workshopName: String,
    craftsmanData: {
        type: Object,
        default: () => ({
            completedProductionUnits: 0,
            estimatedSalary: {
                totalEstimatedSalary: 0,
                baseSalary: 0,
                pieceEarnings: 0,
                pieceRate: 0,
                salaryType: 'fixed_monthly',
                salaryTypeLabel: 'Gaji Pokok Tetap',
            },
            wasteMetrics: {
                totalUnits: 0,
                totalLogs: 0,
            },
            capacityLoad: {
                producedUnits: 0,
                maxCapacity: 500,
                percentage: 0,
                remaining: 500,
                status: 'Optimal & Terkendali',
            },
            lowStockRawMaterials: [],
            recentWorkshopLogs: [],
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
</script>

<template>
    <div class="flex-1 flex flex-col gap-5 min-w-0">
        <!-- Craftsman Header Section -->
        <header class="bg-white/90 backdrop-blur-md rounded-[24px] p-5 border border-[#dce0cd] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#c6ef4e] border border-[#a2c833]"></span>
                    <h1 class="text-xl font-black text-[#0e0f0c] tracking-tight">
                        {{ props.workshopName || 'OptimaBiz Workshop' }}
                    </h1>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#0e0f0c] text-[#c6ef4e]">
                        Workshop Operations & Task View
                    </span>
                </div>
                <p class="text-xs text-[#737868] font-medium mt-1">
                    Kontrol kuantitas fisik sepatu & sandal, estimasi upah pengerjaan, dan pencatatan limbah bahan
                </p>
            </div>

            <!-- Tombol Pintas Aksi: + Input Stok Jadi & ! Laporkan Limbah -->
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                <Link
                    href="/inventory"
                    class="flex-1 sm:flex-none bg-[#0e0f0c] hover:bg-[#20221c] text-white text-xs font-bold px-4 py-2.5 rounded-2xl transition flex items-center justify-center gap-1.5 shadow-sm group cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[17px] text-[#c6ef4e]">inventory_2</span>
                    <span>+ Input Stok Jadi</span>
                </Link>

                <Link
                    href="/inventory"
                    class="flex-1 sm:flex-none bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold px-4 py-2.5 rounded-2xl transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[17px]">delete_sweep</span>
                    <span>! Catat Limbah</span>
                </Link>
            </div>
        </header>

        <!-- 3 Metrik Utama (Atas) -->
        <section class="grid grid-cols-1 sm:grid-cols-3 gap-4" aria-label="Metrik Utama Craftsman">
            <!-- 1. Pasang Sepatu/Sandal Diselesaikan -->
            <div class="bg-white rounded-[22px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-[#737868]">
                        Sepatu / Sandal Selesai
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Bulan Berjalan
                    </span>
                </div>
                <div class="text-2xl font-black text-[#0e0f0c] mt-2">
                    {{ props.craftsmanData?.completedProductionUnits || 0 }} Pasang
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1">
                    Total unit lolos QC & masuk rak jadi
                </div>
            </div>

            <!-- 2. Estimasi Gaji / Upah Bulan Ini -->
            <div class="bg-white rounded-[22px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-[#737868]">
                        Estimasi Payout Upah
                    </span>
                    <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-[#f4f4ed] text-[#0e0f0c] border border-[#dce0cd]">
                        {{ props.craftsmanData?.estimatedSalary?.salaryTypeLabel || 'Gaji Pokok' }}
                    </span>
                </div>
                <div class="text-2xl font-black text-[#0e0f0c] mt-2">
                    {{ formatRupiah(props.craftsmanData?.estimatedSalary?.totalEstimatedSalary) }}
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1">
                    <span v-if="props.craftsmanData?.estimatedSalary?.pieceEarnings > 0">
                        Termasuk borongan: {{ formatRupiah(props.craftsmanData?.estimatedSalary?.pieceEarnings) }}
                    </span>
                    <span v-else>
                        Nominal gaji pokok bulanan aktif
                    </span>
                </div>
            </div>

            <!-- 3. Total Log Limbah Dilaporkan -->
            <div class="bg-white rounded-[22px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-[#737868]">
                        Limbah & Reject Tercatat
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200">
                        {{ props.craftsmanData?.wasteMetrics?.totalLogs || 0 }} Log Tercatat
                    </span>
                </div>
                <div class="text-2xl font-black text-rose-600 mt-2">
                    {{ props.craftsmanData?.wasteMetrics?.totalUnits || 0 }} Unit / Feet
                </div>
                <div class="text-[11px] text-[#737868] font-medium mt-1">
                    Sisa potongan kulit offcut & cacat sol
                </div>
            </div>
        </section>

        <!-- Visualisasi & Analisis: Meteran Kapasitas Bengkel + Daftar Bahan Baku Menipis -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
            <!-- Meteran Kapasitas Bengkel (Capacity Load Bar) - Col 7 -->
            <div class="lg:col-span-7 bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-base font-black text-[#0e0f0c]">Meteran Kapasitas Bengkel</h2>
                            <p class="text-[11px] text-[#737868] font-medium">Beban pengerjaan fisik pengrajin vs batas maksimal bulanan</p>
                        </div>
                        <span
                            :class="[
                                'text-[11px] font-bold px-2.5 py-1 rounded-full border',
                                (props.craftsmanData?.capacityLoad?.percentage || 0) >= 90
                                    ? 'bg-rose-50 text-rose-700 border-rose-200'
                                    : (props.craftsmanData?.capacityLoad?.percentage || 0) >= 75
                                    ? 'bg-amber-50 text-amber-700 border-amber-200'
                                    : 'bg-emerald-50 text-emerald-700 border-emerald-200'
                            ]"
                        >
                            {{ props.craftsmanData?.capacityLoad?.status }}
                        </span>
                    </div>

                    <!-- Progress Bar & Capacity Visualizer -->
                    <div class="mt-6 mb-4">
                        <div class="flex items-center justify-between text-xs font-bold text-[#0e0f0c] mb-2">
                            <span>Produksi Berjalan: {{ props.craftsmanData?.capacityLoad?.producedUnits || 0 }} Pasang</span>
                            <span>Target Max: {{ props.craftsmanData?.capacityLoad?.maxCapacity || 500 }} Pasang</span>
                        </div>
                        <div class="w-full bg-[#f4f4ed] h-5 rounded-full overflow-hidden p-1 border border-[#e1e5d3]">
                            <div
                                class="h-full rounded-full transition-all duration-700 flex items-center justify-end pr-2 text-[10px] font-bold text-white shadow-xs"
                                :class="[
                                    (props.craftsmanData?.capacityLoad?.percentage || 0) >= 90
                                        ? 'bg-rose-600'
                                        : (props.craftsmanData?.capacityLoad?.percentage || 0) >= 75
                                        ? 'bg-amber-500'
                                        : 'bg-[#0e0f0c]'
                                ]"
                                :style="{ width: `${Math.min(100, Math.max(10, props.craftsmanData?.capacityLoad?.percentage || 0))}%` }"
                            >
                                {{ props.craftsmanData?.capacityLoad?.percentage || 0 }}%
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="p-3 bg-[#fafbf7] rounded-xl border border-[#e1e5d3] text-center">
                            <span class="text-[10px] font-bold text-[#737868] uppercase block">Sisa Slot Kapasitas</span>
                            <span class="text-sm font-black text-[#0e0f0c] block mt-0.5">
                                {{ props.craftsmanData?.capacityLoad?.remaining || 0 }} Pasang Lagi
                            </span>
                        </div>
                        <div class="p-3 bg-[#fafbf7] rounded-xl border border-[#e1e5d3] text-center">
                            <span class="text-[10px] font-bold text-[#737868] uppercase block">Status Beban Kerja</span>
                            <span class="text-sm font-black text-emerald-700 block mt-0.5">
                                {{ (props.craftsmanData?.capacityLoad?.percentage || 0) >= 90 ? 'Lembur Diperlukan' : 'Ritme Normal' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-neutral-100 flex items-center justify-between text-[11px] text-[#737868]">
                    <span>Beban dijaga agar pengeleman oven & jahit upper tidak terburu-buru</span>
                    <span class="font-bold text-[#0e0f0c]">Workshop Sepatu & Sandal</span>
                </div>
            </div>

            <!-- Daftar Bahan Baku Menipis - Col 5 -->
            <div class="lg:col-span-5 bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-base font-black text-[#0e0f0c]">Daftar Bahan Baku Menipis</h2>
                            <p class="text-[11px] text-[#737868] font-medium">Peringatan material kulit & sol yang perlu restock</p>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                            Prioritas Gudang
                        </span>
                    </div>

                    <!-- Material Alert List -->
                    <div class="space-y-2 mt-2">
                        <div
                            v-for="mat in props.craftsmanData?.lowStockRawMaterials"
                            :key="mat.id"
                            class="flex items-center justify-between p-2.5 rounded-xl bg-[#fafbf7] border border-[#e5e9db]"
                        >
                            <div class="min-w-0 flex-1 mr-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[9px] font-bold text-[#737868]">{{ mat.sku }}</span>
                                    <h4 class="text-xs font-bold text-[#0e0f0c] truncate">{{ mat.name }}</h4>
                                </div>
                                <span class="text-[10px] text-[#737868]">{{ mat.category }}</span>
                            </div>

                            <div class="text-right shrink-0">
                                <span
                                    :class="[
                                        'text-[10px] font-bold px-2 py-0.5 rounded-full inline-block',
                                        mat.urgency === 'Kritis'
                                            ? 'bg-rose-50 text-rose-700 border border-rose-200'
                                            : 'bg-amber-50 text-amber-700 border border-amber-200'
                                    ]"
                                >
                                    Sisa {{ mat.current_stock }} {{ mat.unit }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="!props.craftsmanData?.lowStockRawMaterials || props.craftsmanData.lowStockRawMaterials.length === 0"
                            class="p-4 text-center text-xs text-[#737868]"
                        >
                            Seluruh stok bahan mentah dalam batas aman.
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-neutral-100 flex items-center justify-between text-[11px]">
                    <span class="text-[#737868]">Koordinasikan pengadaan kulit lembaran</span>
                    <Link href="/inventory" class="font-bold text-[#0e0f0c] hover:underline">
                        Lihat Stok Bahan →
                    </Link>
                </div>
            </div>
        </section>

        <!-- Aksi & Widget: Riwayat Pengerjaan & Limbah Terakhir -->
        <section class="bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-black text-[#0e0f0c]">Riwayat Pengerjaan & Limbah Terakhir</h2>
                    <p class="text-[11px] text-[#737868] font-medium">Log pergerakan fisik barang masuk produksi dan reject waste bengkel</p>
                </div>
                <Link
                    href="/inventory"
                    class="text-xs font-bold text-[#0e0f0c] hover:underline flex items-center gap-1"
                >
                    Log Gudang Lengkap
                    <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                </Link>
            </div>

            <!-- Workshop Log Entries -->
            <div class="space-y-2.5">
                <div
                    v-for="log in props.craftsmanData?.recentWorkshopLogs"
                    :key="log.id"
                    class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-xl bg-[#fafbf7] border border-[#e5e9db] gap-2"
                >
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span
                                :class="[
                                    'text-[9px] font-bold px-2 py-0.5 rounded-full uppercase',
                                    log.type === 'in'
                                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                        : 'bg-rose-50 text-rose-700 border border-rose-200'
                                ]"
                            >
                                {{ log.type === 'in' ? 'Produksi Masuk' : 'Reject / Limbah' }}
                            </span>
                            <span class="text-xs font-bold text-[#0e0f0c]">{{ log.product_name }}</span>
                            <span class="text-[10px] text-[#737868]">({{ log.product_sku }})</span>
                        </div>
                        <div class="text-[11px] text-[#737868] mt-0.5">
                            {{ log.notes }}
                        </div>
                    </div>

                    <div class="text-left sm:text-right shrink-0">
                        <div
                            :class="[
                                'text-sm font-black',
                                log.type === 'in' ? 'text-emerald-700' : 'text-rose-600'
                            ]"
                        >
                            {{ log.type === 'in' ? '+' : '-' }}{{ log.quantity }} {{ log.unit }}
                        </div>
                        <div class="text-[10px] text-[#737868]">
                            {{ log.date }}
                        </div>
                    </div>
                </div>

                <div
                    v-if="!props.craftsmanData?.recentWorkshopLogs || props.craftsmanData.recentWorkshopLogs.length === 0"
                    class="p-6 text-center text-[#737868] text-xs"
                >
                    Belum ada log produksi atau limbah tercatat.
                </div>
            </div>
        </section>
    </div>
</template>

