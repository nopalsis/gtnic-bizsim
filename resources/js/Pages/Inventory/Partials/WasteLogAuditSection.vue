<script setup>
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
    <section class="w-full">
        <!-- Log Pencatatan Limbah Terakhir -->
        <div class="bg-white rounded-2xl p-4 md:p-5 shadow-xs border border-[#dce0cd] flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-[#dce0cd]">
                    <div>
                        <div class="flex items-center gap-1.5 mb-0.5">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-rose-600">
                                Catatan Bahan Rusak & Sisa Kulit
                            </span>
                        </div>
                        <h2 class="text-sm md:text-base font-bold text-[#0e0f0c]">
                            Daftar Bahan Rusak Terakhir
                        </h2>
                    </div>

                    <button
                        type="button"
                        @click="$emit('openWasteModal')"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-[#f2f4ec] border border-[#dce0cd] text-xs font-semibold text-[#0e0f0c] hover:bg-rose-500 hover:text-white hover:border-rose-500 transition-all cursor-pointer"
                    >
                        <span>+ Catat Bahan Rusak</span>
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
                        <p class="text-xs font-medium">Belum ada catatan bahan rusak bulan ini.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
