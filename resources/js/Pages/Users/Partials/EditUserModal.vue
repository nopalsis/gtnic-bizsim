<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    user: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const form = ref({
    name: '',
    email: '',
    role: 'Operational Staff',
    phone: '',
    status: 'active',
    password: '',
});

const errors = ref({});
const isSubmitting = ref(false);

const roles = [
    { value: 'superadmin', label: 'Super Admin (Akses Penuh Finansial & Omzet)' },
    { value: 'admin', label: 'Admin Biasa (Input Data & Operasional - Tanpa Finansial)' },
    { value: 'sales', label: 'Sales Force (Penjualan Grosir, Ritel & Nego Deal)' },
    { value: 'craftsman', label: 'Pengrajin (Craftsman Bengkel)' },
];

const statuses = [
    { value: 'active', label: 'Aktif' },
    { value: 'on_leave', label: 'On Leave (Cuti)' },
    { value: 'inactive', label: 'Nonaktif' },
];

watch(
    () => props.user,
    (newUser) => {
        if (newUser) {
            form.value = {
                name: newUser.name || '',
                email: newUser.email || '',
                role: newUser.role || 'Operational Staff',
                phone: newUser.phone || '',
                status: newUser.status || 'active',
                password: '',
            };
            errors.value = {};
        }
    },
    { immediate: true }
);

const closeModal = () => {
    errors.value = {};
    emit('close');
};

const submit = () => {
    if (!props.user) return;
    isSubmitting.value = true;
    errors.value = {};

    router.put(`/users/${props.user.id}`, form.value, {
        onSuccess: () => {
            isSubmitting.value = false;
            closeModal();
        },
        onError: (err) => {
            errors.value = err;
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <div
        v-if="show && user"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity"
        @click.self="closeModal"
    >
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 md:p-7 overflow-hidden border border-[#e1e5d3] animate-in fade-in zoom-in-95 duration-200">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-[#f0f1eb]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#eeeee7] text-[#0e0f0c] flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-xl">edit</span>
                    </div>
                    <div>
                        <h2 class="font-headline text-lg font-bold text-[#0e0f0c] tracking-tight">Edit Data Staf</h2>
                        <p class="font-body text-xs text-[#868b7d]">Perbarui informasi kontak dan peran operasional.</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="closeModal"
                    class="w-8 h-8 rounded-full bg-[#f4f4ed] hover:bg-[#e8e9e2] text-[#4c4546] hover:text-[#0e0f0c] flex items-center justify-center transition cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <!-- Modal Form -->
            <form @submit.prevent="submit" class="mt-5 space-y-4">
                <!-- Nama Lengkap -->
                <div>
                    <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1.5">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full px-4 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] border border-transparent focus:border-[#0e0f0c] transition"
                    />
                    <p v-if="errors.name" class="text-rose-600 text-[11px] mt-1">{{ errors.name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1.5">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full px-4 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] border border-transparent focus:border-[#0e0f0c] transition"
                    />
                    <p v-if="errors.email" class="text-rose-600 text-[11px] mt-1">{{ errors.email }}</p>
                </div>

                <!-- Peran & Status (2 Columns) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <!-- Peran -->
                    <div>
                        <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1.5">
                            Peran / Jabatan <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.role"
                            class="w-full px-3.5 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] border border-transparent focus:border-[#0e0f0c] transition"
                        >
                            <option v-for="r in roles" :key="r.value" :value="r.value">
                                {{ r.label }}
                            </option>
                        </select>
                        <p v-if="errors.role" class="text-rose-600 text-[11px] mt-1">{{ errors.role }}</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1.5">
                            Status Akun <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.status"
                            class="w-full px-3.5 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] border border-transparent focus:border-[#0e0f0c] transition"
                        >
                            <option v-for="s in statuses" :key="s.value" :value="s.value">
                                {{ s.label }}
                            </option>
                        </select>
                        <p v-if="errors.status" class="text-rose-600 text-[11px] mt-1">{{ errors.status }}</p>
                    </div>
                </div>

                <!-- Nomor Kontak -->
                <div>
                    <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1.5">
                        Nomor Kontak (WhatsApp / Telepon)
                    </label>
                    <input
                        v-model="form.phone"
                        type="text"
                        class="w-full px-4 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] border border-transparent focus:border-[#0e0f0c] transition"
                    />
                    <p v-if="errors.phone" class="text-rose-600 text-[11px] mt-1">{{ errors.phone }}</p>
                </div>

                <!-- Password Baru (Opsional) -->
                <div>
                    <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1.5">
                        Password Baru <span class="text-[#868b7d] font-normal">(kosongkan jika tidak diubah)</span>
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        placeholder="Masukkan password baru jika ingin mengganti"
                        class="w-full px-4 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] border border-transparent focus:border-[#0e0f0c] transition"
                    />
                    <p v-if="errors.password" class="text-rose-600 text-[11px] mt-1">{{ errors.password }}</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#f0f1eb]">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-5 py-2.5 rounded-full bg-[#f4f4ed] hover:bg-[#e8e9e2] text-[#0e0f0c] font-label text-xs font-semibold transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="px-6 py-2.5 rounded-full bg-[#0e0f0c] hover:bg-[#20221c] text-white font-label text-xs font-semibold shadow-md transition cursor-pointer disabled:opacity-50 flex items-center gap-2"
                    >
                        <span v-if="isSubmitting" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ isSubmitting ? 'Memperbarui...' : 'Simpan Perubahan' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

