<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    category: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const isEditing = ref(false);

const form = useForm({
    name: '',
    type: 'finished',
    prefix_sku: 'FG-SHO',
    description: '',
    is_active: true,
});

watch(
    () => props.category,
    (val) => {
        if (val) {
            isEditing.value = true;
            form.name = val.name || '';
            form.type = val.type || 'finished';
            form.prefix_sku = val.prefix_sku || '';
            form.description = val.description === '-' ? '' : (val.description || '');
            form.is_active = val.is_active ?? true;
        } else {
            isEditing.value = false;
            form.reset();
            form.type = 'finished';
            form.prefix_sku = 'FG-SHO';
            form.is_active = true;
        }
    },
    { immediate: true }
);

// Auto suggest prefix SKU based on type
const onTypeChange = () => {
    if (!isEditing.value) {
        if (form.type === 'finished') {
            form.prefix_sku = 'FG-SHO';
        } else {
            form.prefix_sku = 'RAW-LEA';
        }
    }
};

const submit = () => {
    if (isEditing.value && props.category) {
        form.put(`/categories/${props.category.id}`, {
            onSuccess: () => emit('close'),
        });
    } else {
        form.post('/categories', {
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
        @click.self="$emit('close')"
    >
        <div class="bg-white rounded-2xl max-w-lg w-full p-5 md:p-6 shadow-xl border border-[#dce0cd] my-8 max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3.5 border-b border-[#f0f1eb]">
                <div>
                    <span class="text-[10px] text-[#506600] uppercase tracking-wider font-bold">
                        Master Data Kategori
                    </span>
                    <h2 class="text-lg md:text-xl font-bold text-[#0e0f0c]">
                        {{ isEditing ? 'Edit Kategori: ' + (props.category?.name || '') : 'Tambah Kategori Baru' }}
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

            <!-- Form Body -->
            <form @submit.prevent="submit" class="mt-5 space-y-4">
                <!-- Nama Kategori -->
                <div>
                    <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                        Nama Kategori <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Contoh: Sepatu Boots Pria / Lembaran Kulit Veg-Tan"
                        required
                        class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] placeholder:text-[#737868] text-xs md:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition"
                    />
                    <p v-if="form.errors.name" class="text-rose-600 text-[11px] mt-1">{{ form.errors.name }}</p>
                </div>

                <!-- Jenis Kategori & Awalan SKU -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                            Jenis Kategori <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.type"
                            @change="onTypeChange"
                            class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] text-xs md:text-sm border border-[#dce0cd] focus:outline-none cursor-pointer font-medium"
                        >
                            <option value="finished">Sepatu & Sandal Jadi</option>
                            <option value="raw">Bahan Baku, Sol & Lem</option>
                        </select>
                        <p v-if="form.errors.type" class="text-rose-600 text-[11px] mt-1">{{ form.errors.type }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                            Awalan Kode SKU
                        </label>
                        <input
                            v-model="form.prefix_sku"
                            type="text"
                            placeholder="Contoh: FG-SHO / RAW-LEA"
                            class="w-full px-4 py-2.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] placeholder:text-[#737868] text-xs md:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition uppercase font-mono"
                        />
                        <span class="text-[10px] text-[#737868] block mt-1">Dipakai otomatis saat input barang baru</span>
                        <p v-if="form.errors.prefix_sku" class="text-rose-600 text-[11px] mt-1">{{ form.errors.prefix_sku }}</p>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                        Keterangan / Catatan <span class="text-[#737868] font-normal">(opsional)</span>
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        placeholder="Catatan jenis barang yang masuk ke dalam kelompok kategori ini..."
                        class="w-full px-4 py-2.5 rounded-2xl bg-[#f2f4ec] text-[#0e0f0c] placeholder:text-[#737868] text-xs md:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition"
                    ></textarea>
                    <p v-if="form.errors.description" class="text-rose-600 text-[11px] mt-1">{{ form.errors.description }}</p>
                </div>

                <!-- Status Aktif Toggle -->
                <div class="flex items-center gap-3 pt-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="form.is_active"
                            class="sr-only peer"
                        />
                        <div class="w-10 h-6 bg-neutral-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0e0f0c]"></div>
                    </label>
                    <div>
                        <span class="text-xs font-semibold text-[#0e0f0c] block">Status Kategori Aktif</span>
                        <span class="text-[10px] text-[#737868]">Kategori aktif dapat langsung dipilih saat menambah barang baru</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-[#f0f1eb]">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="px-4 py-2 rounded-full border border-[#dce0cd] text-xs font-semibold text-[#0e0f0c] hover:bg-[#f2f4ec] transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2 rounded-full bg-[#0e0f0c] text-white text-xs font-bold hover:bg-[#20221c] transition-all disabled:opacity-50 cursor-pointer shadow-xs inline-flex items-center gap-1.5"
                    >
                        <span v-if="form.processing" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span>{{ isEditing ? 'Simpan Perubahan' : 'Simpan Kategori' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

