<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

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
const isDeleting = ref(false);

const handleDelete = () => {
    if (!props.category) return;
    isDeleting.value = true;
    router.delete(`/categories/${props.category.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleting.value = false;
            emit('close');
        },
        onError: () => {
            isDeleting.value = false;
        },
    });
};
</script>

<template>
    <div
        v-if="props.show && props.category"
        class="fixed inset-0 z-50 bg-[#0e0f0c]/60 backdrop-blur-xs flex items-center justify-center p-4"
        @click.self="$emit('close')"
    >
        <div class="bg-white rounded-2xl max-w-md w-full p-5 md:p-6 shadow-xl border border-[#dce0cd]">
            <!-- Warning Header -->
            <div class="flex items-start gap-3.5 mb-4">
                <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">warning</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-[#0e0f0c]">
                        Hapus Kategori "{{ props.category.name }}"?
                    </h3>
                    <p class="text-xs text-[#737868] mt-1 leading-relaxed">
                        Tindakan ini akan menghapus kategori secara permanen dari sistem.
                    </p>
                </div>
            </div>

            <!-- Warning if Category has Products -->
            <div
                v-if="props.category.products_count > 0"
                class="p-3.5 bg-amber-50 rounded-xl border border-amber-200 mb-4"
            >
                <div class="flex items-center gap-2 text-amber-900 text-xs font-bold mb-1">
                    <span class="material-symbols-outlined text-[16px]">info</span>
                    <span>Kategori Sedang Digunakan</span>
                </div>
                <p class="text-[11px] text-amber-800 leading-relaxed">
                    Kategori ini memiliki <span class="font-bold">{{ props.category.products_count }} barang</span> terdaftar. Kategori yang masih berisi produk tidak dapat dihapus demi keamanan data. Sebaiknya Anda menonaktifkan statusnya saja.
                </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button
                    type="button"
                    @click="$emit('close')"
                    class="px-4 py-2 rounded-full border border-[#dce0cd] text-xs font-semibold text-[#0e0f0c] hover:bg-[#f2f4ec] transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button
                    v-if="props.category.products_count === 0"
                    type="button"
                    @click="handleDelete"
                    :disabled="isDeleting"
                    class="px-4 py-2 rounded-full bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 transition-colors cursor-pointer disabled:opacity-50 inline-flex items-center gap-1.5"
                >
                    <span v-if="isDeleting" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    <span>Ya, Hapus Kategori</span>
                </button>
            </div>
        </div>
    </div>
</template>

