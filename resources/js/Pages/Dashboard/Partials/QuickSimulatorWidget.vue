<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    baseline: {
        type: Object,
        default: () => ({
            avgPricePerPair: 450000,
            avgCostPerPair: 220000,
            fixedCost: 22500000,
            wasteCost: 1500000,
            currentVolume: 250,
            maxCapacity: 500,
            currentNetProfit: 35000000,
        }),
    },
});

const additionalPairs = ref(40);
const negotiatedDiscount = ref(5); // in percent

const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};

// Simulation computations
const simulatedDealPrice = computed(() => {
    const base = props.baseline?.avgPricePerPair || 450000;
    const discountFactor = (100 - negotiatedDiscount.value) / 100;
    return Math.round(base * discountFactor);
});

const additionalRevenue = computed(() => {
    return additionalPairs.value * simulatedDealPrice.value;
});

const additionalCost = computed(() => {
    const cost = props.baseline?.avgCostPerPair || 220000;
    return additionalPairs.value * cost;
});

const additionalProfit = computed(() => {
    return additionalRevenue.value - additionalCost.value;
});

const totalProjectedVolume = computed(() => {
    return (props.baseline?.currentVolume || 0) + additionalPairs.value;
});

const projectedCapacityUsage = computed(() => {
    const max = props.baseline?.maxCapacity || 500;
    return max > 0 ? Math.round((totalProjectedVolume.value / max) * 100) : 0;
});
</script>

<template>
    <div class="bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm flex flex-col justify-between">
        <div>
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-[#0e0f0c] text-[#c6ef4e] flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">tune</span>
                    </span>
                    <div>
                        <h3 class="text-sm font-black text-[#0e0f0c]">Quick Simulator Order Partai</h3>
                        <p class="text-[11px] text-[#737868]">Uji instan penawaran harga & kuantitas pesanan baru</p>
                    </div>
                </div>
                <Link
                    href="/simulator"
                    class="text-[11px] font-bold text-[#0e0f0c] hover:underline flex items-center gap-1"
                >
                    Simulator Lengkap
                    <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                </Link>
            </div>

            <!-- Sliders Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <!-- Additional Pairs Slider -->
                <div class="bg-[#f4f4ed] p-3 rounded-2xl border border-[#e1e5d3]">
                    <div class="flex items-center justify-between text-xs font-bold text-[#0e0f0c] mb-1.5">
                        <span class="text-[#737868]">Kuantitas Tambahan</span>
                        <span class="px-2 py-0.5 rounded-full bg-white text-[#0e0f0c] border border-[#dce0cd] font-black">
                            +{{ additionalPairs }} Pasang
                        </span>
                    </div>
                    <input
                        v-model.number="additionalPairs"
                        type="range"
                        min="0"
                        max="200"
                        step="5"
                        class="w-full accent-[#0e0f0c] cursor-pointer"
                    />
                    <div class="flex justify-between text-[10px] text-[#737868] mt-1 font-medium">
                        <span>0 psg</span>
                        <span>100 psg</span>
                        <span>200 psg</span>
                    </div>
                </div>

                <!-- Negotiated Discount Slider -->
                <div class="bg-[#f4f4ed] p-3 rounded-2xl border border-[#e1e5d3]">
                    <div class="flex items-center justify-between text-xs font-bold text-[#0e0f0c] mb-1.5">
                        <span class="text-[#737868]">Diskon Tawar (Nego)</span>
                        <span
                            :class="[
                                'px-2 py-0.5 rounded-full border font-black',
                                negotiatedDiscount <= 10
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                    : 'bg-rose-50 text-rose-700 border-rose-200'
                            ]"
                        >
                            {{ negotiatedDiscount }}% Diskon
                        </span>
                    </div>
                    <input
                        v-model.number="negotiatedDiscount"
                        type="range"
                        min="0"
                        max="25"
                        step="1"
                        class="w-full accent-[#0e0f0c] cursor-pointer"
                    />
                    <div class="flex justify-between text-[10px] text-[#737868] mt-1 font-medium">
                        <span>0% (Penuh)</span>
                        <span>10% (Aman)</span>
                        <span>25% (Risiko)</span>
                    </div>
                </div>
            </div>

            <!-- Projection Impact Matrix -->
            <div class="grid grid-cols-3 gap-2.5 pt-2">
                <div class="bg-[#fafbf7] p-2.5 rounded-xl border border-[#e1e5d3] text-center">
                    <span class="text-[10px] font-bold text-[#737868] uppercase block">Harga Deal / Psg</span>
                    <span class="text-xs font-black text-[#0e0f0c] block mt-0.5">
                        {{ formatRupiah(simulatedDealPrice) }}
                    </span>
                </div>
                <div class="bg-[#fafbf7] p-2.5 rounded-xl border border-[#e1e5d3] text-center">
                    <span class="text-[10px] font-bold text-[#737868] uppercase block">Tambahan Omzet</span>
                    <span class="text-xs font-black text-emerald-700 block mt-0.5">
                        +{{ formatRupiah(additionalRevenue) }}
                    </span>
                </div>
                <div class="bg-[#0e0f0c] p-2.5 rounded-xl text-center text-white">
                    <span class="text-[10px] font-bold text-[#c6ef4e] uppercase block">Proyeksi Δ Laba</span>
                    <span class="text-xs font-black text-white block mt-0.5">
                        +{{ formatRupiah(additionalProfit) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Capacity Warning / Status Indicator -->
        <div class="mt-4 pt-3 border-t border-[#f0f1eb] flex items-center justify-between text-xs">
            <div class="flex items-center gap-1.5 text-[#737868]">
                <span class="material-symbols-outlined text-[16px]">precision_manufacturing</span>
                <span>Beban Kapasitas:</span>
                <span class="font-bold text-[#0e0f0c]">{{ totalProjectedVolume }} / {{ props.baseline?.maxCapacity || 500 }} Pasang</span>
            </div>
            <span
                :class="[
                    'text-[10px] font-bold px-2 py-0.5 rounded-full',
                    projectedCapacityUsage > 90
                        ? 'bg-rose-100 text-rose-800'
                        : projectedCapacityUsage > 75
                        ? 'bg-amber-100 text-amber-800'
                        : 'bg-emerald-100 text-emerald-800'
                ]"
            >
                {{ projectedCapacityUsage }}% Terpakai
            </span>
        </div>
    </div>
</template>

