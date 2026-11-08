<script setup>
const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
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
    <section class="grid grid-cols-1 md:grid-cols-3 gap-3.5 md:gap-4">
        <!-- KPI 1: Total Nilai Modal Stok -->
        <div class="bg-white rounded-2xl p-4 shadow-xs border border-[#dce0cd] flex flex-col justify-between hover:border-[#c6ef4e] transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] md:text-[11px] uppercase tracking-wider text-[#737868] font-bold">
                    Total Nilai Modal Stok
                </span>
                <div class="w-8 h-8 rounded-full bg-[#f2f4ec] flex items-center justify-center text-[#0e0f0c]">
                    <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2 mb-1.5">
                    <span class="text-xl md:text-2xl font-bold text-[#0e0f0c] tracking-tight">
                        {{ formatRupiah(props.stats.totalStockValuation) }}
                    </span>
                </div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-[11px] font-semibold mb-1.5">
                    <span class="material-symbols-outlined text-[14px] text-[#506600]">trending_up</span>
                    <span>{{ props.stats.valuationChangePct || '+5.4% vs bulan lalu' }}</span>
                </div>
                <p class="text-[11px] text-[#737868]">
                    Total modal dari {{ props.stats.totalProductsCount || 0 }} barang yang ada di bengkel & toko
                </p>
            </div>
        </div>

        <!-- KPI 2: Signature Neon Lime Card (Peringatan Stok Menipis) -->
        <div class="bg-[#c6ef4e] rounded-2xl p-4 shadow-xs flex flex-col justify-between relative overflow-hidden text-[#0e0f0c]">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] md:text-[11px] uppercase tracking-wider text-[#0e0f0c]/80 font-bold">
                    Stok Menipis / Mau Habis
                </span>
                <span class="px-2 py-0.5 rounded-full bg-[#0e0f0c] text-white text-[10px] uppercase tracking-wider font-bold shadow-xs">
                    Perlu Ditambah
                </span>
            </div>
            <div>
                <div class="flex items-baseline gap-2 mb-1">
                    <span class="text-xl md:text-2xl font-bold text-[#0e0f0c] tracking-tight">
                        {{ props.stats.lowStockCount || 0 }} Barang Hampir Habis
                    </span>
                </div>
                <p class="text-xs text-[#0e0f0c] mb-1 font-semibold line-clamp-1" :title="props.stats.lowStockNames">
                    {{ props.stats.lowStockNames || 'Semua stok dalam kondisi aman' }}
                </p>
                <p class="text-[11px] text-[#0e0f0c]/80">
                    Segera pesan tambahan bahan kulit atau buat stok sepatu baru
                </p>
            </div>
        </div>

        <!-- KPI 3: Nilai Bahan Rusak / Terbuang -->
        <div class="bg-white rounded-2xl p-4 shadow-xs border border-[#dce0cd] flex flex-col justify-between hover:border-rose-300 transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] md:text-[11px] uppercase tracking-wider text-[#737868] font-bold">
                    Nilai Bahan Rusak / Terbuang
                </span>
                <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">delete_sweep</span>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2 mb-1.5">
                    <span class="text-xl md:text-2xl font-bold text-rose-600 tracking-tight">
                        {{ formatRupiah(props.stats.totalWasteCost) }}
                    </span>
                </div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-[11px] font-semibold mb-1.5">
                    <span class="material-symbols-outlined text-[14px] text-[#506600]">arrow_downward</span>
                    <span>{{ props.stats.wasteChangePct || '-4.2% efisiensi membaik' }}</span>
                </div>
                <p class="text-[11px] text-[#737868]">
                    Bahan cacat potong, gagal lem sol, atau sisa kulit ({{ props.stats.totalWasteUnits || 0 }} unit terbuang)
                </p>
            </div>
        </div>
    </section>
</template>
