<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    products: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

const selectedReason = ref('Cacat potong pola vamps');

const form = useForm({
    product_id: '',
    quantity: 1,
    movement_date: new Date().toISOString().slice(0, 10),
    notes: '',
});

// Pilih produk default saat modal terbuka
watch(
    () => props.show,
    (val) => {
        if (val && props.products.length > 0 && !form.product_id) {
            form.product_id = props.products[0].id;
        }
    }
);

// Detail produk terpilih
const selectedProduct = computed(() => {
    return props.products.find((p) => p.id === Number(form.product_id)) || null;
});

// Kalkulasi otomatis kerugian: HPP * Quantity
const calculatedLoss = computed(() => {
    if (!selectedProduct.value) return 0;
    return (selectedProduct.value.cost_price || 0) * (Number(form.quantity) || 0);
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

const submit = () => {
    // Gabungkan alasan ke dalam notes
    const finalNotes = form.notes ? `${selectedReason.value}: ${form.notes}` : selectedReason.value;
    form.transform((data) => ({
        ...data,
        notes: finalNotes,
    })).post('/inventory/waste', {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <div
        v-if="props.show"
        class="fixed inset-0 z-50 bg-[#0e0f0c]/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
    >
        <div class="bg-white rounded-2xl max-w-xl w-full p-5 md:p-6 shadow-xl border border-[#dce0cd] my-8 max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3.5 border-b border-[#f0f1eb]">
                <div>
                    <span class="text-[10px] text-rose-600 uppercase tracking-wider font-bold">
                        Catat Kerugian Operasional
                    </span>
                    <h2 class="text-lg md:text-xl font-bold text-[#0e0f0c]">
                        Form Catat Limbah (Waste Log)
                    </h2>
                </div>
                <button
                    type="button"
                    @click="$emit('close')"
                    class="w-10 h-10 rounded-full hover:bg-[#f2f4ec] text-[#737868] hover:text-[#0e0f0c] flex items-center justify-center transition-colors cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <!-- Form Body -->
            <form @submit.prevent="submit" class="mt-6 space-y-4">
                <!-- Pilih Bahan atau Produk Rusak -->
                <div>
                    <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                        Pilih Bahan atau Produk Rusak <span class="text-rose-500">*</span>
                    </label>
                    <select
                        v-model="form.product_id"
                        required
                        class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-xs md:text-sm border border-[#dce0cd] focus:outline-none cursor-pointer"
                    >
                        <option
                            v-for="p in props.products"
                            :key="p.id"
                            :value="p.id"
                        >
                            {{ p.name }} (HPP: {{ formatRupiah(p.cost_price) }} / {{ p.unit }}) - Stok: {{ p.current_stock }}
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Jumlah Terbuang / Residu -->
                    <div>
                        <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                            Jumlah Terbuang / Residu <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                v-model.number="form.quantity"
                                type="number"
                                min="1"
                                required
                                class="w-full px-4 pr-16 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-xs md:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition"
                            />
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-[#737868] font-medium pointer-events-none">
                                {{ selectedProduct?.unit || 'unit' }}
                            </span>
                        </div>
                    </div>

                    <!-- Tanggal Kejadian -->
                    <div>
                        <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                            Tanggal Kejadian <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.movement_date"
                            type="date"
                            required
                            class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-xs md:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition"
                        />
                    </div>
                </div>

                <!-- Alasan Kerusakan / Limbah -->
                <div>
                    <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                        Alasan Kerusakan / Limbah <span class="text-rose-500">*</span>
                    </label>
                    <select
                        v-model="selectedReason"
                        class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-xs md:text-sm border border-[#dce0cd] focus:outline-none cursor-pointer"
                    >
                        <option value="Cacat potong pola vamps">Cacat potong pola vamps (sisa offcut)</option>
                        <option value="Kulit berlubang & cacat tekstur alami">Kulit berlubang / cacat tekstur alami</option>
                        <option value="Gagal jahit sol & welt pecah">Gagal jahit sol & welt pecah</option>
                        <option value="Noda lem permanen & sol gagal rekat">Noda lem permanen & sol gagal rekat</option>
                        <option value="Sobek saat lasting cetakan kayu">Sobek saat penarikan cetakan kayu (lasting)</option>
                        <option value="Goresan pisau seset terlalu dalam">Goresan pisau seset (skiving) terlalu dalam</option>
                        <option value="Residu wajar potongan pinggiran">Residu wajar potongan pinggiran kulit</option>
                    </select>
                </div>

                <!-- Kalkulasi Otomatis Nominal Kerugian (Live Dynamic Box) -->
                <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-[#0e0f0c] flex items-center justify-between shadow-2xs">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-rose-700 font-bold block">
                            Kalkulasi Otomatis Nominal Kerugian
                        </span>
                        <p class="text-lg md:text-xl font-bold text-rose-600 mt-0.5">
                            {{ formatRupiah(calculatedLoss) }}
                        </p>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]">calculate</span>
                    </div>
                </div>

                <!-- Catatan Tambahan Pengrajin -->
                <div>
                    <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                        Catatan Tambahan Pengrajin
                    </label>
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        placeholder="Tuliskan nomor lembar kulit atau pengrajin yang bertugas saat insiden..."
                        class="w-full p-3.5 rounded-2xl bg-[#f2f4ec] text-[#0e0f0c] placeholder:text-[#737868] text-xs md:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition"
                    ></textarea>
                </div>

                <!-- Modal Footer Actions -->
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#f0f1eb] mt-6">
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
                        class="px-6 py-2.5 rounded-full bg-rose-600 text-white hover:bg-rose-700 text-xs font-semibold shadow-xs disabled:opacity-50 cursor-pointer flex items-center gap-2"
                    >
                        <span v-if="form.processing" class="material-symbols-outlined text-xs animate-spin">progress_activity</span>
                        <span>Catat Kerugian</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

