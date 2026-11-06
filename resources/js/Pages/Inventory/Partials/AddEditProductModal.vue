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

const isEditing = ref(false);

const form = useForm({
    name: '',
    sku: '',
    category: 'Bahan Baku Kulit',
    unit: 'sq ft',
    cost_price: 38000,
    base_price: 45000,
    current_stock: 50,
    min_stock_alert: 15,
});

watch(
    () => props.product,
    (val) => {
        if (val) {
            isEditing.value = true;
            form.name = val.name || '';
            form.sku = val.sku || '';
            form.category = val.category || 'Bahan Baku Kulit';
            form.unit = val.unit || 'sq ft';
            form.cost_price = val.cost_price || 0;
            form.base_price = val.base_price || 0;
            form.current_stock = val.current_stock || 0;
            form.min_stock_alert = val.min_stock_alert || 10;
        } else {
            isEditing.value = false;
            form.reset();
        }
    },
    { immediate: true }
);

const submit = () => {
    if (isEditing.value && props.product) {
        form.put(`/inventory/product/${props.product.id}`, {
            onSuccess: () => emit('close'),
        });
    } else {
        form.post('/inventory/product', {
            onSuccess: () => {
                form.reset();
                emit('close');
            },
        });
    }
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
                    <span class="text-[10px] text-[#506600] uppercase tracking-wider font-bold">
                        Master Data Stok & Material
                    </span>
                    <h2 class="text-lg md:text-xl font-bold text-[#0e0f0c]">
                        {{ isEditing ? 'Edit: ' + (props.product?.name || 'Barang') : 'Tambah Barang / Material Baru' }}
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
                <!-- Nama Barang -->
                <div>
                    <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                        Nama Barang / Material <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Contoh: Kulit Chromexcel Burgundy 5-6oz"
                        required
                        class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] placeholder:text-[#737868] text-xs md:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition"
                    />
                    <p v-if="form.errors.name" class="text-rose-600 text-[11px] mt-1">{{ form.errors.name }}</p>
                </div>

                <!-- SKU & Kategori -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                            Kode SKU <span class="text-[#737868] font-normal">(opsional)</span>
                        </label>
                        <input
                            v-model="form.sku"
                            type="text"
                            placeholder="Otomatis jika kosong"
                            class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] placeholder:text-[#737868] text-xs md:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition uppercase"
                            :disabled="isEditing"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                            Kategori Barang <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.category"
                            class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-xs md:text-sm border border-[#dce0cd] focus:outline-none cursor-pointer"
                        >
                            <option value="Bahan Baku Kulit">Bahan Baku Kulit (Lembaran)</option>
                            <option value="Komponen Sol">Komponen Sol & Hak</option>
                            <option value="Bahan Pendukung">Bahan Pendukung (Lem / Kimia)</option>
                            <option value="Sepatu Pria">Sepatu Pria (Produk Jadi)</option>
                            <option value="Sepatu Wanita">Sepatu Wanita (Produk Jadi)</option>
                            <option value="Sandal Kulit Pria">Sandal Kulit Pria</option>
                            <option value="Sandal Kulit Wanita">Sandal Kulit Wanita</option>
                            <option value="Perawatan & Aksesoris">Perawatan & Aksesoris</option>
                        </select>
                    </div>
                </div>

                <!-- Satuan Ukur & Batas Minimum -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                            Satuan Ukur <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.unit"
                            class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-xs md:text-sm border border-[#dce0cd] focus:outline-none cursor-pointer"
                        >
                            <option value="sq ft">sq ft (Square Feet)</option>
                            <option value="pasang">Pasang (Pairs)</option>
                            <option value="kaleng">Kaleng / Botol</option>
                            <option value="pcs">Pcs (Satuan)</option>
                            <option value="meter">Meter</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                            Batas Minimum Alert <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model.number="form.min_stock_alert"
                            type="number"
                            min="0"
                            required
                            placeholder="Contoh: 10"
                            class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-xs md:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition"
                        />
                    </div>
                </div>

                <!-- Modal HPP & Harga Jual -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                            Modal HPP per Satuan (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model.number="form.cost_price"
                            type="number"
                            min="0"
                            required
                            placeholder="Contoh: 38000"
                            class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-xs md:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                            Harga Banderol / Jual (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model.number="form.base_price"
                            type="number"
                            min="0"
                            required
                            placeholder="Contoh: 45000"
                            class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-xs md:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition"
                        />
                    </div>
                </div>

                <!-- Stok Awal (Khusus Tambah Baru) -->
                <div v-if="!isEditing">
                    <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                        Stok Fisik Awal Masuk
                    </label>
                    <input
                        v-model.number="form.current_stock"
                        type="number"
                        min="0"
                        placeholder="Contoh: 50"
                        class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-xs md:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition"
                    />
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
                        class="px-6 py-2.5 rounded-full bg-[#0e0f0c] text-white hover:bg-[#20221c] text-xs font-semibold shadow-xs disabled:opacity-50 cursor-pointer flex items-center gap-2"
                    >
                        <span v-if="form.processing" class="material-symbols-outlined text-xs animate-spin">progress_activity</span>
                        <span>{{ isEditing ? 'Simpan Perubahan' : 'Simpan Barang' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

