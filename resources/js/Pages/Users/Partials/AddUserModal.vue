<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const hasBaseSalary = ref(true);
const hasIncentive = ref(true);

const form = useForm({
    name: '',
    email: '',
    role: 'craftsman',
    phone: '',
    salary_type: 'hybrid',
    base_salary: 2000000,
    piece_rate: 20000,
    is_active: true,
    password: '',
});

const syncSalaryType = () => {
    if (hasBaseSalary.value && hasIncentive.value) {
        form.salary_type = 'hybrid';
    } else if (hasIncentive.value) {
        form.salary_type = 'piece_rate';
    } else {
        form.salary_type = 'fixed_monthly';
    }
};

const toggleBaseSalary = () => {
    if (hasBaseSalary.value && !hasIncentive.value) {
        hasIncentive.value = true;
        hasBaseSalary.value = false;
    } else {
        hasBaseSalary.value = !hasBaseSalary.value;
    }
    syncSalaryType();
};

const toggleIncentive = () => {
    if (hasIncentive.value && !hasBaseSalary.value) {
        hasBaseSalary.value = true;
        hasIncentive.value = false;
    } else {
        hasIncentive.value = !hasIncentive.value;
    }
    syncSalaryType();
};

const formatRupiah = (number) => {
    if (number === null || number === undefined || isNaN(number)) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};

// Proyeksi kuantitas output standar untuk staf baru
const projectedOutputQuantity = computed(() => {
    if (form.role === 'craftsman') return 80;
    if (form.role === 'sales') return 10;
    return 0;
});

const outputUnitLabel = computed(() => {
    if (form.role === 'sales') return 'deals';
    return 'pasang';
});

// Komponen 1: Gaji Pokok
const calculatedBaseSalary = computed(() => {
    if (!hasBaseSalary.value) return 0;
    return parseFloat(form.base_salary) || 0;
});

// Komponen 2: Insentif Borongan Output
const calculatedIncentive = computed(() => {
    if (!hasIncentive.value) return 0;
    const rate = parseFloat(form.piece_rate) || 0;
    return rate * projectedOutputQuantity.value;
});

// Komponen 3: Total Take-Home Pay Proyeksi
const calculatedTotalPayout = computed(() => {
    return calculatedBaseSalary.value + calculatedIncentive.value;
});

const closeModal = () => {
    form.reset();
    form.clearErrors();
    hasBaseSalary.value = true;
    hasIncentive.value = true;
    emit('close');
};

const submit = () => {
    syncSalaryType();
    form.post('/users', {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity"
        @click.self="closeModal"
    >
        <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full p-6 md:p-8 overflow-y-auto max-h-[90vh] border border-[#e1e5d3] animate-in fade-in zoom-in-95 duration-200">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-[#f0f1eb]">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-[#0e0f0c] text-[#c6ef4e] flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-2xl">person_add</span>
                    </div>
                    <div>
                        <h2 class="font-headline text-lg font-bold text-[#0e0f0c] tracking-tight">
                            Tambah Anggota Staf Baru
                        </h2>
                        <p class="font-body text-xs text-[#868b7d]">
                            Daftarkan akun staf workshop, atur peran dan skema pengupahannya.
                        </p>
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
                <!-- 1. DATA PRIBADI & ROLE -->
                <div>
                    <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Contoh: Pak Bambang"
                        class="w-full px-3.5 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] border border-transparent focus:border-[#0e0f0c] transition"
                    />
                    <p v-if="form.errors.name" class="text-rose-600 text-[11px] mt-1">{{ form.errors.name }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1">
                            Alamat Email <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="bambang@optimabiz.test"
                            class="w-full px-3.5 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] border border-transparent focus:border-[#0e0f0c] transition"
                        />
                        <p v-if="form.errors.email" class="text-rose-600 text-[11px] mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1">
                            Nomor Telepon / WhatsApp
                        </label>
                        <input
                            v-model="form.phone"
                            type="text"
                            placeholder="+62 812-3456-7890"
                            class="w-full px-3.5 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] border border-transparent focus:border-[#0e0f0c] transition"
                        />
                        <p v-if="form.errors.phone" class="text-rose-600 text-[11px] mt-1">{{ form.errors.phone }}</p>
                    </div>
                </div>

                <!-- Role Workshop -->
                <div>
                    <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1">
                        Role Workshop <span class="text-rose-500">*</span>
                    </label>
                    <select
                        v-model="form.role"
                        class="w-full px-3.5 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] border border-transparent focus:border-[#0e0f0c] transition"
                    >
                        <option value="superadmin">Super Admin / Owner (Akses Penuh Finansial & Omzet)</option>
                        <option value="admin">Admin Biasa / Operasional (Input Data, Produk & Mutasi - Tanpa Finansial)</option>
                        <option value="sales">Sales Force (Penjualan Grosir, Ritel & Negosiasi Deal)</option>
                        <option value="craftsman">Pengrajin Bengkel (Tukang Pola, Seset, Jahit, Sol & Lasting)</option>
                    </select>
                    <p v-if="form.errors.role" class="text-rose-600 text-[11px] mt-1">{{ form.errors.role }}</p>
                </div>

                <!-- 2. SKEMA PAYROLL WITH 2-OPTION TOGGLE & TOTAL CARD -->
                <div class="pt-4 border-t border-[#f0f1eb] space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block font-label text-xs font-bold text-[#0e0f0c] uppercase tracking-wider">
                            Pilihan Komponen Upah (Payroll)
                        </label>
                        <span class="text-[10px] font-semibold text-[#506600] bg-[#c9f250]/40 px-2 py-0.5 rounded-full">
                            {{ hasBaseSalary && hasIncentive ? 'Pokok + Insentif' : (hasBaseSalary ? 'Gaji Pokok' : 'Insentif Borongan') }}
                        </span>
                    </div>

                    <!-- 2-Option Toggle Selector: Gaji Pokok & Insentif Saja -->
                    <div class="grid grid-cols-2 gap-2 p-1 bg-[#f4f4ed] rounded-2xl">
                        <button
                            type="button"
                            @click="toggleBaseSalary"
                            :class="[
                                'py-2.5 px-3 rounded-xl font-label text-xs font-semibold transition-all cursor-pointer flex items-center justify-center gap-2',
                                hasBaseSalary
                                    ? 'bg-[#0e0f0c] text-white shadow-xs'
                                    : 'bg-transparent text-[#868b7d] hover:text-[#0e0f0c]'
                            ]"
                        >
                            <span class="material-symbols-outlined text-base" :class="hasBaseSalary ? 'text-[#c6ef4e]' : ''">
                                {{ hasBaseSalary ? 'check_circle' : 'add_circle' }}
                            </span>
                            <span>Gaji Pokok Bulanan</span>
                        </button>

                        <button
                            type="button"
                            @click="toggleIncentive"
                            :class="[
                                'py-2.5 px-3 rounded-xl font-label text-xs font-semibold transition-all cursor-pointer flex items-center justify-center gap-2',
                                hasIncentive
                                    ? 'bg-[#0e0f0c] text-white shadow-xs'
                                    : 'bg-transparent text-[#868b7d] hover:text-[#0e0f0c]'
                            ]"
                        >
                            <span class="material-symbols-outlined text-base" :class="hasIncentive ? 'text-[#c6ef4e]' : ''">
                                {{ hasIncentive ? 'check_circle' : 'add_circle' }}
                            </span>
                            <span>Insentif Borongan</span>
                        </button>
                    </div>

                    <!-- Input Nominal Upah Berdasarkan yang Aktif -->
                    <div class="space-y-3 pt-1">
                        <!-- Input Gaji Pokok (Muncul jika hasBaseSalary aktif) -->
                        <div v-if="hasBaseSalary">
                            <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1">
                                Gaji Pokok Bulanan (Rp / bulan)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-headline text-xs font-bold text-[#868b7d]">
                                    Rp
                                </span>
                                <input
                                    v-model.number="form.base_salary"
                                    type="number"
                                    min="0"
                                    step="50000"
                                    placeholder="2000000"
                                    class="w-full pl-10 pr-4 py-2 bg-[#f4f4ed] rounded-xl text-xs md:text-sm font-headline font-bold text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] transition"
                                />
                            </div>
                            <p class="text-[11px] text-[#868b7d] mt-1">
                                Terformat: <strong class="text-[#0e0f0c]">{{ formatRupiah(form.base_salary) }}</strong> / bulan.
                            </p>
                            <p v-if="form.errors.base_salary" class="text-rose-600 text-[11px] mt-1">{{ form.errors.base_salary }}</p>
                        </div>

                        <!-- Input Tarif Borongan / Insentif (Muncul jika hasIncentive aktif) -->
                        <div v-if="hasIncentive">
                            <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1">
                                {{ form.role === 'sales' ? 'Insentif per Deal Closing (Rp / deal)' : 'Tarif Insentif Borongan (Rp / pasang jadi)' }}
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-headline text-xs font-bold text-[#868b7d]">
                                    Rp
                                </span>
                                <input
                                    v-model.number="form.piece_rate"
                                    type="number"
                                    min="0"
                                    step="1000"
                                    placeholder="20000"
                                    class="w-full pl-10 pr-4 py-2 bg-[#f4f4ed] rounded-xl text-xs md:text-sm font-headline font-bold text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] transition"
                                />
                            </div>
                            <p class="text-[11px] text-[#868b7d] mt-1">
                                Terformat: <strong class="text-[#0e0f0c]">{{ formatRupiah(form.piece_rate) }}</strong> per {{ outputUnitLabel }} selesai.
                            </p>
                            <p v-if="form.errors.piece_rate" class="text-rose-600 text-[11px] mt-1">{{ form.errors.piece_rate }}</p>
                        </div>
                    </div>

                    <!-- LIVE BENTO CARD: KALKULASI TOTAL PAYOUT -->
                    <div class="mt-4 p-4 rounded-2xl bg-[#0e0f0c] text-white shadow-md space-y-3">
                        <div class="flex items-center justify-between pb-2.5 border-b border-white/10">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-[#c6ef4e]/20 text-[#c6ef4e] flex items-center justify-center">
                                    <span class="material-symbols-outlined text-base">receipt_long</span>
                                </div>
                                <div>
                                    <span class="font-headline text-xs font-bold text-white block">
                                        Kalkulasi Total Payout Staf
                                    </span>
                                    <span class="text-[10px] text-neutral-400">
                                        Rincian otomatis berdasarkan komponen upah aktif
                                    </span>
                                </div>
                            </div>

                            <span
                                :class="[
                                    'text-[10px] font-semibold px-2.5 py-0.5 rounded-full',
                                    hasBaseSalary && hasIncentive
                                        ? 'bg-[#c6ef4e] text-[#0e0f0c]'
                                        : (hasBaseSalary ? 'bg-blue-500/20 text-blue-300' : 'bg-amber-500/20 text-amber-300')
                                ]"
                            >
                                {{ hasBaseSalary && hasIncentive ? 'Pokok + Insentif' : (hasBaseSalary ? 'Gaji Pokok' : 'Insentif Borongan') }}
                            </span>
                        </div>

                        <!-- Rincian dari Masing-Masing Komponen -->
                        <div class="space-y-2 text-xs">
                            <!-- 1. Gaji Pokok -->
                            <div class="flex items-center justify-between py-1.5 px-3 rounded-xl" :class="hasBaseSalary ? 'bg-white/5' : 'opacity-40'">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full" :class="hasBaseSalary ? 'bg-blue-400' : 'bg-neutral-600'"></span>
                                    <div>
                                        <span class="text-neutral-300 font-medium text-xs">Gaji Pokok Bulanan:</span>
                                        <span class="text-[10px] text-neutral-400 block">
                                            {{ hasBaseSalary ? 'Uang kehadiran & tunjangan dasar' : 'Tidak diaktifkan' }}
                                        </span>
                                    </div>
                                </div>
                                <span class="font-headline text-xs md:text-sm font-bold text-white">
                                    {{ formatRupiah(calculatedBaseSalary) }}
                                </span>
                            </div>

                            <!-- 2. Insentif Output -->
                            <div class="flex items-center justify-between py-1.5 px-3 rounded-xl" :class="hasIncentive ? 'bg-white/5' : 'opacity-40'">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full" :class="hasIncentive ? 'bg-[#c6ef4e]' : 'bg-neutral-600'"></span>
                                    <div>
                                        <span class="text-neutral-300 font-medium text-xs">Insentif Output Kerja:</span>
                                        <span class="text-[10px] text-neutral-400 block">
                                            {{ hasIncentive ? `Proyeksi ${projectedOutputQuantity} ${outputUnitLabel} × ${formatRupiah(form.piece_rate)}` : 'Tidak diaktifkan' }}
                                        </span>
                                    </div>
                                </div>
                                <span class="font-headline text-xs md:text-sm font-bold" :class="hasIncentive ? 'text-[#c6ef4e]' : 'text-neutral-500'">
                                    + {{ formatRupiah(calculatedIncentive) }}
                                </span>
                            </div>

                            <!-- 3. Grand Total -->
                            <div class="pt-2 mt-1 border-t border-white/10 flex items-center justify-between px-1">
                                <div>
                                    <span class="font-label text-[11px] font-bold text-neutral-200 uppercase tracking-wider block">
                                        Total Payout Proyeksi:
                                    </span>
                                    <span class="text-[10px] text-neutral-400">
                                        {{ hasBaseSalary && hasIncentive ? 'Gaji Pokok + Insentif Borongan' : (hasBaseSalary ? 'Gaji Pokok Bulanan' : 'Upah Borongan Murni') }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="font-headline text-xl md:text-2xl font-black text-[#c6ef4e]">
                                        {{ formatRupiah(calculatedTotalPayout) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password Akun -->
                <div class="pt-3">
                    <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1">
                        Password Awal <span class="text-[#868b7d] font-normal">(opsional, default: password123)</span>
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        placeholder="Minimal 6 karakter"
                        class="w-full px-3.5 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] transition"
                    />
                    <p v-if="form.errors.password" class="text-rose-600 text-[11px] mt-1">{{ form.errors.password }}</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-5 border-t border-[#f0f1eb]">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-5 py-2.5 rounded-full bg-[#f4f4ed] hover:bg-[#e8e9e2] text-[#0e0f0c] font-label text-xs font-semibold transition cursor-pointer"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 rounded-full bg-[#0e0f0c] hover:bg-[#20221c] text-white font-label text-xs font-semibold shadow-md transition cursor-pointer disabled:opacity-50 flex items-center gap-2"
                    >
                        <span v-if="form.processing" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ form.processing ? 'Menyimpan...' : 'Daftarkan Staf' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
