<script setup>
const props = defineProps({
    show: Boolean,
    product: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'apply-action']);

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
    <div
        v-if="show && product"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0e0f0c]/60 backdrop-blur-sm"
        @click.self="emit('close')"
    >
        <div class="bg-white rounded-[28px] w-full max-w-xl shadow-2xl border border-[#dce0cd] overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Modal Header -->
            <div class="p-6 border-b border-neutral-100 flex items-center justify-between bg-[#fafbf7]">
                <div class="flex items-center gap-3">
                    <div
                        class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl font-bold shrink-0"
                        :class="[
                            product.status === 'kritis'
                                ? 'bg-rose-100 text-rose-800'
                                : product.status === 'waspada'
                                    ? 'bg-amber-100 text-amber-800'
                                    : 'bg-emerald-100 text-emerald-800'
                        ]"
                    >
                        {{ product.status === 'kritis' ? '🔴' : (product.status === 'waspada' ? '🟡' : '🟢') }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-bold text-neutral-400">{{ product.sku }}</span>
                            <span
                                class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                                :class="[
                                    product.status === 'kritis'
                                        ? 'bg-rose-600 text-white'
                                        : product.status === 'waspada'
                                            ? 'bg-amber-500 text-white'
                                            : 'bg-emerald-600 text-white'
                                ]"
                            >
                                Status: {{ product.statusLabel }}
                            </span>
                        </div>
                        <h3 class="text-xl font-black text-[#0e0f0c] tracking-tight mt-0.5">
                            {{ product.name }}
                        </h3>
                    </div>
                </div>

                <button
                    type="button"
                    @click="emit('close')"
                    class="w-9 h-9 rounded-full bg-neutral-200 hover:bg-neutral-300 flex items-center justify-center text-neutral-700 cursor-pointer font-bold transition-colors"
                >
                    ✕
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-6 overflow-y-auto space-y-5">
                <!-- Comparison Bar: Target vs Realisasi -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3.5 rounded-2xl bg-[#fafbf7] border border-[#eaecdf]">
                        <div class="text-[11px] font-bold text-[#737868] uppercase tracking-wider">Target Margin Katalog</div>
                        <div class="text-lg font-black text-[#0e0f0c] mt-0.5">
                            {{ product.targetMargin }}
                        </div>
                        <div class="text-[11px] text-[#737868]">Kondisi ideal bengkel</div>
                    </div>
                    <div
                        class="p-3.5 rounded-2xl border"
                        :class="[
                            product.status === 'kritis'
                                ? 'bg-rose-50 border-rose-200 text-rose-950'
                                : product.status === 'waspada'
                                    ? 'bg-amber-50 border-amber-200 text-amber-950'
                                    : 'bg-emerald-50 border-emerald-200 text-emerald-950'
                        ]"
                    >
                        <div class="text-[11px] font-bold uppercase tracking-wider opacity-80">Realisasi Riil Lapangan</div>
                        <div class="text-lg font-black mt-0.5">
                            {{ product.realMargin }}
                        </div>
                        <div class="text-[11px] font-bold" :class="product.status === 'kritis' ? 'text-rose-700' : 'text-neutral-600'">
                            {{ product.status === 'kritis' ? 'Terjadi kebocoran laba' : (product.status === 'waspada' ? 'Margin tergerus' : 'Laba aman') }}
                        </div>
                    </div>
                </div>

                <!-- Rincian Pembentuk HPP Riil -->
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-[#4d5240] mb-2.5 flex items-center gap-1.5">
                        <span>📋</span> Rincian Pembentuk HPP Riil per Pasang
                    </h4>

                    <div class="bg-[#fafbf7] rounded-2xl border border-[#eaecdf] divide-y divide-[#eaecdf] text-xs">
                        <!-- 1. Modal Bahan Baku Asli -->
                        <div class="p-3.5 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-[#0e0f0c]">1. Modal Bahan Baku Asli</span>
                                <p class="text-[11px] text-[#737868]">Kulit mentah, sol karet/kayu, lem & aksesoris</p>
                            </div>
                            <span class="font-black text-[#0e0f0c] text-sm">
                                {{ formatRupiah(product.hppBreakdown?.materialCost) }}
                            </span>
                        </div>

                        <!-- 2. Rata-rata Beban Limbah Kulit -->
                        <div
                            class="p-3.5 flex items-center justify-between"
                            :class="product.hppBreakdown?.isWasteLeaking ? 'bg-rose-50/70' : ''"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-[#0e0f0c]">2. Rata-rata Beban Limbah Kulit</span>
                                    <span
                                        v-if="product.hppBreakdown?.isWasteLeaking"
                                        class="px-2 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-extrabold uppercase animate-pulse"
                                    >
                                        Bocor di Sini
                                    </span>
                                </div>
                                <p class="text-[11px]" :class="product.hppBreakdown?.isWasteLeaking ? 'text-rose-700 font-medium' : 'text-[#737868]'">
                                    {{ product.hppBreakdown?.wasteCostNote || 'Limbah potongan pola & cacat seset' }}
                                </p>
                            </div>
                            <span
                                class="font-black text-sm"
                                :class="product.hppBreakdown?.isWasteLeaking ? 'text-rose-700' : 'text-[#0e0f0c]'"
                            >
                                +{{ formatRupiah(product.hppBreakdown?.wasteCost) }}
                            </span>
                        </div>

                        <!-- 3. Upah Pengrajin -->
                        <div class="p-3.5 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-[#0e0f0c]">3. Upah Pengrajin (Labor)</span>
                                <p class="text-[11px] text-[#737868]">Tukang seset, jahit pola upper, lasting sol</p>
                            </div>
                            <span class="font-black text-[#0e0f0c] text-sm">
                                {{ formatRupiah(product.hppBreakdown?.laborCost) }}
                            </span>
                        </div>

                        <!-- 4. Rata-rata Diskon Nego Sales -->
                        <div
                            class="p-3.5 flex items-center justify-between"
                            :class="product.hppBreakdown?.isDiscountLeaking ? 'bg-rose-50/70' : ''"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-[#0e0f0c]">4. Rata-rata Diskon Nego Sales</span>
                                    <span
                                        v-if="product.hppBreakdown?.isDiscountLeaking"
                                        class="px-2 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-extrabold uppercase animate-pulse"
                                    >
                                        Bocor di Sini
                                    </span>
                                </div>
                                <p class="text-[11px]" :class="product.hppBreakdown?.isDiscountLeaking ? 'text-rose-700 font-medium' : 'text-[#737868]'">
                                    {{ product.hppBreakdown?.salesDiscountNote || 'Potongan harga tawar kesepakatan sales' }}
                                </p>
                            </div>
                            <span
                                class="font-black text-sm"
                                :class="product.hppBreakdown?.isDiscountLeaking ? 'text-rose-700' : 'text-[#0e0f0c]'"
                            >
                                -{{ formatRupiah(product.hppBreakdown?.salesDiscount) }}
                            </span>
                        </div>

                        <!-- 5. Keuntungan Bersih Riil -->
                        <div
                            class="p-4 flex items-center justify-between rounded-b-2xl"
                            :class="[
                                product.status === 'kritis'
                                    ? 'bg-rose-100 text-rose-950'
                                    : product.status === 'waspada'
                                        ? 'bg-amber-100 text-amber-950'
                                        : 'bg-[#c6ef4e]/20 text-[#2d4000]'
                            ]"
                        >
                            <div>
                                <div class="font-black text-sm">Keuntungan Bersih Riil</div>
                                <div class="text-[11px] font-semibold mt-0.5 opacity-80">
                                    {{ product.hppBreakdown?.netProfitNote || 'Laba bersih setelah semua beban riil' }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-black">
                                    {{ formatRupiah(product.hppBreakdown?.realNetProfit) }}
                                </div>
                                <div class="text-[10px] font-bold">
                                    Target: {{ formatRupiah(product.targetMarginRp) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Diagnosa & Rekomendasi Lapangan -->
                <div class="bg-[#fafbf7] rounded-2xl p-4 border border-[#eaecdf] space-y-2 text-xs">
                    <div>
                        <span class="font-black text-[#0e0f0c] flex items-center gap-1.5">
                            <span>🔍</span> Temuan Auditor Lapangan:
                        </span>
                        <p class="text-[#737868] mt-1 leading-relaxed">
                            {{ product.hppBreakdown?.diagnosis }}
                        </p>
                    </div>

                    <div class="pt-2 border-t border-[#eaecdf]">
                        <span class="font-black text-[#2d4000] flex items-center gap-1.5">
                            <span>💡</span> Rekomendasi Tindakan:
                        </span>
                        <p class="text-[#4d5240] font-semibold mt-1 leading-relaxed">
                            {{ product.hppBreakdown?.actionRecommendation }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 bg-[#fafbf7] border-t border-[#eaecdf] flex items-center justify-end gap-3">
                <button
                    type="button"
                    @click="emit('close')"
                    class="px-5 py-2.5 rounded-xl border border-[#dce0cd] bg-white text-[#0e0f0c] text-xs font-bold hover:bg-neutral-50 cursor-pointer transition-colors"
                >
                    Tutup
                </button>
                <button
                    type="button"
                    @click="emit('apply-action', product); emit('close');"
                    class="px-5 py-2.5 rounded-xl bg-[#0e0f0c] text-white text-xs font-black hover:bg-[#2d4000] cursor-pointer transition-colors flex items-center gap-1.5 shadow-sm"
                >
                    <span>⚡</span> Terapkan Rekomendasi Tindakan
                </button>
            </div>
        </div>
    </div>
</template>

