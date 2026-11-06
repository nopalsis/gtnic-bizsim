<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    product: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const mutationType = ref('in'); // 'in' | 'out'
const mutationReason = ref('Restock Pembelian Bahan Baru');

const form = useForm({
    product_id: '',
    type: 'in',
    quantity: 10,
    notes: '',
});

watch(
    () => props.product,
    (prod) => {
        if (prod) {
            form.product_id = prod.id;
            form.quantity = 10;
            mutationType.value = 'in';
            mutationReason.value = 'Restock Pembelian Bahan Baru';
        }
    },
    { immediate: true }
);

const submit = () => {
    form.type = mutationType.value;
    form.notes = mutationReason.value;
    form.post('/inventory/mutation', {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <div
        v-if="props.show && props.product"
        class="fixed inset-0 z-50 bg-[#0e0f0c]/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
    >
        <div class="bg-white rounded-2xl max-w-md w-full p-5 md:p-6 shadow-xl border border-[#dce0cd] my-8">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-[#f0f1eb]">
                <div>
                    <span class="text-[10px] text-[#737868] uppercase tracking-wider font-bold">
                        Penyesuaian Fisik Gudang
                    </span>
                    <h2 class="text-base md:text-lg font-bold text-[#0e0f0c] truncate max-w-[280px]" :title="props.product.name">
                        Mutasi: {{ props.product.name }}
                    </h2>
                </div>
                <button
                    type="button"
                    @click="$emit('close')"
                    class="w-9 h-9 rounded-full hover:bg-[#f2f4ec] text-[#737868] hover:text-[#0e0f0c] flex items-center justify-center transition-colors cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>

            <!-- Current Stock Info -->
            <div class="my-4 p-3 rounded-2xl bg-[#f2f4ec] flex items-center justify-between text-xs">
                <span class="text-[#737868]">Stok Fisik Saat Ini:</span>
                <span class="font-bold text-[#0e0f0c] text-sm">
                    {{ props.product.current_stock }} {{ props.product.unit }}
                </span>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Radio Masuk / Keluar -->
                <div class="grid grid-cols-2 gap-2">
                    <label
                        :class="[
                            'flex items-center justify-center gap-2 p-2.5 rounded-full border transition cursor-pointer text-xs font-semibold select-none',
                            mutationType === 'in'
                                ? 'bg-[#c6ef4e]/30 border-[#506600] text-[#3b4d00]'
                                : 'bg-[#f2f4ec] border-transparent text-[#737868] hover:bg-[#eaecdf]'
                        ]"
                    >
                        <input
                            v-model="mutationType"
                            type="radio"
                            value="in"
                            class="sr-only"
                        />
                        <span class="material-symbols-outlined text-base">add_circle</span>
                        <span>Masuk (+)</span>
                    </label>

                    <label
                        :class="[
                            'flex items-center justify-center gap-2 p-2.5 rounded-full border transition cursor-pointer text-xs font-semibold select-none',
                            mutationType === 'out'
                                ? 'bg-rose-50 border-rose-400 text-rose-700'
                                : 'bg-[#f2f4ec] border-transparent text-[#737868] hover:bg-[#eaecdf]'
                        ]"
                    >
                        <input
                            v-model="mutationType"
                            type="radio"
                            value="out"
                            class="sr-only"
                        />
                        <span class="material-symbols-outlined text-base">remove_circle</span>
                        <span>Keluar (-)</span>
                    </label>
                </div>

                <!-- Jumlah Mutasi -->
                <div>
                    <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                        Jumlah Mutasi ({{ props.product.unit }}) <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model.number="form.quantity"
                        type="number"
                        min="1"
                        required
                        placeholder="Jumlah"
                        class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-xs md:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition"
                    />
                </div>

                <!-- Alasan Mutasi -->
                <div>
                    <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                        Alasan / Keterangan Mutasi <span class="text-rose-500">*</span>
                    </label>
                    <select
                        v-model="mutationReason"
                        class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-xs md:text-sm border border-[#dce0cd] focus:outline-none cursor-pointer"
                    >
                        <option value="Restock Pembelian Bahan Baru">Restock Pembelian Baru</option>
                        <option value="Pengambilan untuk Produksi Sepatu">Pengambilan untuk Produksi Sepatu / Sandal</option>
                        <option value="Batch Produksi Selesai QC">Batch Produksi Selesai QC</option>
                        <option value="Retur / Pengembalian Supplier">Retur / Pengembalian Supplier</option>
                        <option value="Koreksi Selisih Opname Fisik">Koreksi Selisih Opname Fisik</option>
                    </select>
                </div>

                <!-- Modal Actions -->
                <div class="pt-3 flex items-center justify-end gap-2 border-t border-[#f0f1eb] mt-5">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="px-5 py-2.5 rounded-full bg-[#f2f4ec] hover:bg-[#eaecdf] text-[#0e0f0c] text-xs font-semibold transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 rounded-full bg-[#0e0f0c] text-white hover:bg-[#20221c] text-xs font-semibold shadow-xs disabled:opacity-50 cursor-pointer flex items-center gap-1.5"
                    >
                        <span v-if="form.processing" class="material-symbols-outlined text-xs animate-spin">progress_activity</span>
                        <span>Simpan Mutasi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

