<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    baseline: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['close']);

const isSaving = ref(false);

const settingsForm = ref({
    workshop_name: props.baseline?.workshopName || 'OptimaBiz Leathercraft & Shoes',
    employee_count: props.baseline?.employeeCount || 5,
    salary_per_employee: props.baseline?.salaryPerEmployee || 3200000,
    monthly_fixed_cost: props.baseline?.monthlyFixedCost || 6500000,
    monthly_max_capacity: props.baseline?.monthlyMaxCapacity || 500,
});

const submitSettings = () => {
    isSaving.value = true;
    router.post('/simulator/settings', settingsForm.value, {
        onSuccess: () => {
            isSaving.value = false;
            emit('close');
        },
        onError: () => {
            isSaving.value = false;
        },
    });
};
</script>

<template>
    <div
        v-if="props.show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0e0f0c]/60 backdrop-blur-sm"
        @click.self="emit('close')"
    >
        <div class="bg-white rounded-[28px] w-full max-w-lg shadow-2xl border border-[#dce0cd] overflow-hidden">
            <!-- Modal Header -->
            <div class="p-6 border-b border-neutral-100 flex items-center justify-between bg-[#fafbf7]">
                <div>
                    <h3 class="text-xl font-black text-[#0e0f0c]">Pengaturan Kapasitas & Biaya Bengkel</h3>
                    <p class="text-xs text-neutral-500 mt-0.5">Atur jumlah tukang, standar gaji, biaya sewa, dan kapasitas maksimal sepatu</p>
                </div>
                <button
                    type="button"
                    @click="emit('close')"
                    class="w-9 h-9 rounded-full bg-neutral-200 hover:bg-neutral-300 flex items-center justify-center text-neutral-700 cursor-pointer font-bold"
                >
                    ✕
                </button>
            </div>

            <!-- Form Body -->
            <form @submit.prevent="submitSettings" class="p-6 space-y-4">
                <div>
                    <label class="block text-[11px] font-bold text-neutral-600 mb-1">Nama Bengkel Sepatu</label>
                    <input
                        v-model="settingsForm.workshop_name"
                        type="text"
                        required
                        class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-neutral-600 mb-1">Jumlah Tukang Tetap</label>
                        <input
                            v-model.number="settingsForm.employee_count"
                            type="number"
                            min="1"
                            max="50"
                            required
                            class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-bold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                        />
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-neutral-600 mb-1">Gaji per Tukang (Rp/bln)</label>
                        <input
                            v-model.number="settingsForm.salary_per_employee"
                            type="number"
                            step="50000"
                            min="0"
                            required
                            class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-bold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-neutral-600 mb-1">Biaya Sewa & Listrik (Rp)</label>
                        <input
                            v-model.number="settingsForm.monthly_fixed_cost"
                            type="number"
                            step="100000"
                            min="0"
                            required
                            class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-bold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                        />
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-neutral-600 mb-1">Kapasitas Maksimal (Pasang/Bulan)</label>
                        <input
                            v-model.number="settingsForm.monthly_max_capacity"
                            type="number"
                            min="10"
                            required
                            class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-bold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                        />
                    </div>
                </div>

                <div class="pt-3 border-t border-neutral-100 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        @click="emit('close')"
                        class="px-4 py-2 text-xs font-bold text-neutral-600 hover:text-neutral-900 cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="isSaving"
                        class="bg-[#0e0f0c] hover:bg-neutral-800 text-white font-bold text-xs px-5 py-2.5 rounded-full transition cursor-pointer shadow-sm disabled:opacity-50"
                    >
                        {{ isSaving ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

