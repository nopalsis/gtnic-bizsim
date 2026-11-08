<script setup>
const props = defineProps({
    metrics: {
        type: Object,
        default: () => ({
            total_active_staff: 0,
            total_sales: 0,
            total_sales_deals: 0,
            total_sales_revenue: 0,
            total_craftsmen: 0,
            total_craftsmen_pairs: 0,
            monthly_max_capacity: 500,
            capacity_utilization: 0,
            total_monthly_payroll_est: 0,
        }),
    },
    counts: {
        type: Object,
        default: () => ({
            all: 0,
            admin: 0,
            sales: 0,
            craftsman: 0,
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
        <!-- Bento Card 1: Total Active Staff & Payroll -->
        <div class="bg-white rounded-2xl p-3.5 md:p-4 border border-[#e1e5d3] shadow-xs relative overflow-hidden flex flex-col justify-between hover:border-[#c6ef4e] transition-all">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <span class="font-label text-[10px] md:text-[11px] font-bold text-[#868b7d] uppercase tracking-wider block">
                        Tenaga Kerja Aktif
                    </span>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span class="font-headline text-2xl md:text-[26px] font-black text-[#0e0f0c] leading-tight">
                            {{ metrics.total_active_staff }}
                        </span>
                        <span class="font-body text-[11px] text-[#868b7d]">
                            / {{ counts.all }} total staf
                        </span>
                    </div>
                </div>

                <div class="w-9 h-9 rounded-xl bg-[#0e0f0c] text-[#c6ef4e] flex items-center justify-center shadow-xs shrink-0">
                    <span class="material-symbols-outlined text-lg">groups</span>
                </div>
            </div>

            <div class="mt-2.5 pt-2 border-t border-[#f4f4ed]">
                <div class="flex items-center justify-between text-[11px]">
                    <span class="font-body text-[#868b7d]">Perkiraan Gaji:</span>
                    <span class="font-headline font-bold text-[#0e0f0c]">
                        {{ formatRupiah(metrics.total_monthly_payroll_est) }}
                    </span>
                </div>
                <div class="flex items-center gap-1.5 mt-1.5">
                    <span class="px-1.5 py-0.5 rounded bg-[#eeeee7] text-[10px] font-semibold text-[#0e0f0c]">
                        {{ counts.admin }} Admin
                    </span>
                    <span class="px-1.5 py-0.5 rounded bg-[#c9f250]/30 text-[10px] font-semibold text-[#3b4d00]">
                        {{ metrics.total_sales }} Sales
                    </span>
                    <span class="px-1.5 py-0.5 rounded bg-[#e3e3dc] text-[10px] font-semibold text-[#1a1c18]">
                        {{ metrics.total_craftsmen }} Pengrajin
                    </span>
                </div>
            </div>
        </div>

        <!-- Bento Card 2: Sales Force Performance -->
        <div class="bg-white rounded-2xl p-3.5 md:p-4 border border-[#e1e5d3] shadow-xs relative overflow-hidden flex flex-col justify-between hover:border-[#c6ef4e] transition-all">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <span class="font-label text-[10px] md:text-[11px] font-bold text-[#868b7d] uppercase tracking-wider block">
                        Tim Sales & Kasir
                    </span>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span class="font-headline text-2xl md:text-[26px] font-black text-[#0e0f0c] leading-tight">
                            {{ metrics.total_sales }}
                        </span>
                        <span class="font-body text-[10px] md:text-[11px] text-[#506600] font-semibold bg-[#c9f250]/40 px-2 py-0.5 rounded-full inline-block">
                            {{ metrics.total_sales_deals }} Penjualan Bulan Ini
                        </span>
                    </div>
                </div>

                <div class="w-9 h-9 rounded-xl bg-[#eeeee7] text-[#0e0f0c] flex items-center justify-center shadow-xs shrink-0">
                    <span class="material-symbols-outlined text-lg">point_of_sale</span>
                </div>
            </div>

            <div class="mt-2.5 pt-2 border-t border-[#f4f4ed]">
                <div class="flex items-center justify-between text-[11px]">
                    <span class="font-body text-[#868b7d]">Penjualan Sales:</span>
                    <span class="font-headline font-bold text-[#0e0f0c]">
                        {{ formatRupiah(metrics.total_sales_revenue) }}
                    </span>
                </div>
                <div class="text-[10px] text-[#868b7d] mt-1.5 truncate">
                    Penjualan eceran dan pesanan partai grosir
                </div>
            </div>
        </div>

        <!-- Bento Card 3: Workshop Craftsmen & Capacity -->
        <div class="bg-white rounded-2xl p-3.5 md:p-4 border border-[#e1e5d3] shadow-xs relative overflow-hidden flex flex-col justify-between hover:border-[#c6ef4e] transition-all">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <span class="font-label text-[10px] md:text-[11px] font-bold text-[#868b7d] uppercase tracking-wider block">
                        Tukang Sepatu & Kapasitas
                    </span>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span class="font-headline text-2xl md:text-[26px] font-black text-[#0e0f0c] leading-tight">
                            {{ metrics.total_craftsmen }}
                        </span>
                        <span class="font-body text-[11px] text-[#868b7d]">
                            Tukang Sol & Jahit
                        </span>
                    </div>
                </div>

                <div class="w-9 h-9 rounded-xl bg-[#0e0f0c] text-[#c6ef4e] flex items-center justify-center shadow-xs shrink-0">
                    <span class="material-symbols-outlined text-lg">precision_manufacturing</span>
                </div>
            </div>

            <div class="mt-2.5 pt-2 border-t border-[#f4f4ed]">
                <div class="flex items-center justify-between text-[11px] mb-1.5">
                    <span class="font-body text-[#868b7d]">Kapasitas Terpakai:</span>
                    <span class="font-headline font-bold text-[#0e0f0c]">
                        {{ metrics.total_craftsmen_pairs }}/{{ metrics.monthly_max_capacity }} psg ({{ metrics.capacity_utilization }}%)
                    </span>
                </div>
                <!-- Progress Bar Kapasitas -->
                <div class="w-full bg-[#f4f4ed] h-1.5 rounded-full overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all duration-500"
                        :class="metrics.capacity_utilization > 90 ? 'bg-amber-500' : 'bg-[#c6ef4e]'"
                        :style="{ width: Math.min(metrics.capacity_utilization, 100) + '%' }"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>
