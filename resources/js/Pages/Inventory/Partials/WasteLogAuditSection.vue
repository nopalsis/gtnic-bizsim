<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    recentWasteLogs: {
        type: Array,
        default: () => [],
    },
    advisory: {
        type: Object,
        default: () => ({}),
    },
});

defineEmits(['openWasteModal']);

const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};

const getWasteIcon = (notes) => {
    const n = (notes || '').toLowerCase();
    if (n.includes('potong') || n.includes('pola')) return 'content_cut';
    if (n.includes('lem') || n.includes('oven')) return 'sanitizer';
    if (n.includes('lasting') || n.includes('sobek')) return 'texture';
    return 'warning';
};
</script>

<template>
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <!-- Panel Kiri: Residu & Kegagalan Pola Potong (Log Pencatatan Limbah Terakhir) -->
        <div class="lg:col-span-8 bg-white rounded-2xl p-4 md:p-5 shadow-xs border border-[#dce0cd] flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-[#dce0cd]">
                    <div>
                        <div class="flex items-center gap-1.5 mb-0.5">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-rose-600">
                                Log Pencatatan Limbah Terakhir
                            </span>
                        </div>
                        <h2 class="text-sm md:text-base font-bold text-[#0e0f0c]">
                            Residu & Kegagalan Pola Potong
                        </h2>
                    </div>

                    <button
                        type="button"
                        @click="$emit('openWasteModal')"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-[#f2f4ec] border border-[#dce0cd] text-xs font-semibold text-[#0e0f0c] hover:bg-rose-500 hover:text-white hover:border-rose-500 transition-all cursor-pointer"
                    >
                        <span>Buka Form Limbah</span>
                        <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                    </button>
                </div>

                <div class="space-y-2.5">
                    <div
                        v-for="log in props.recentWasteLogs"
                        :key="log.id"
                        class="flex items-center justify-between p-3 rounded-xl bg-[#f2f4ec] hover:bg-[#eaecdf] transition-colors border border-[#dce0cd]/50"
                    >
                        <div class="flex items-center gap-2.5 min-w-0 pr-2">
                            <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[16px]">{{ getWasteIcon(log.notes) }}</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs md:text-sm font-semibold text-[#0e0f0c] truncate">
                                    {{ log.product_name }}
                                </p>
                                <p class="text-[11px] text-[#737868] truncate" :title="log.notes">
                                    Terbuang: {{ log.quantity }} {{ log.unit }} • Alasan: {{ log.notes }}
                                </p>
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="text-xs md:text-sm text-rose-600 font-bold block">
                                - {{ formatRupiah(log.cost_impact) }}
                            </span>
                            <span class="text-[10px] text-[#737868]">
                                {{ log.date }}
                            </span>
                        </div>
                    </div>

                    <div v-if="props.recentWasteLogs.length === 0" class="py-6 text-center text-[#737868]">
                        <p class="text-xs font-medium">Belum ada riwayat limbah yang tercatat bulan ini.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Advisori Optimasi Bahan (AI Material Advisory Card) -->
        <div class="lg:col-span-4 bg-[#0e0f0c] text-white rounded-2xl p-4 md:p-5 shadow-xs flex flex-col justify-between relative overflow-hidden">
            <!-- Decorative Glow Ring -->
            <div class="absolute -right-10 -bottom-10 w-36 h-36 rounded-full bg-[#c6ef4e]/20 blur-2xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full bg-[#c6ef4e]"></span>
                    <span class="text-[10px] text-[#c6ef4e] uppercase tracking-wider font-bold">
                        Advisori Optimasi Bahan
                    </span>
                </div>

                <h3 class="text-base md:text-lg font-bold text-white mb-1.5 leading-snug">
                    {{ props.advisory?.title || 'Gunakan Residu Potongan Kulit Nabati untuk Gantungan Kunci & Dompet Kartu' }}
                </h3>

                <p class="text-xs text-[#eaecdf]/80 mb-3 leading-relaxed">
                    {{ props.advisory?.description || 'Residu sisa potongan lembaran kulit berukuran >10cm² dapat diolah kembali menjadi produk turunan mikro senilai Rp 350.000 dibanding dicatat sebagai kerugian murni.' }}
                </p>
            </div>

            <div class="pt-3 flex items-center justify-between border-t border-neutral-800 relative z-10">
                <span class="text-xs text-[#c6ef4e] font-bold">
                    {{ props.advisory?.efficiency_gain || 'Efisiensi +18.2%' }}
                </span>

                <Link
                    :href="props.advisory?.action_url || '/simulator'"
                    class="px-4 py-2 rounded-full bg-[#c6ef4e] text-[#0e0f0c] hover:bg-white text-xs font-bold transition shadow-xs cursor-pointer inline-flex items-center gap-1.5"
                >
                    <span>{{ props.advisory?.action_label || 'Simulasikan Daur Residu' }}</span>
                    <span class="material-symbols-outlined text-[15px]">trending_up</span>
                </Link>
            </div>
        </div>
    </section>
</template>
