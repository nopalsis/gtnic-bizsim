<script setup>
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

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

// Update form fields saat user yang dipilih berubah
watch(
    () => props.user,
    (val) => {
        if (val) {
            form.name = val.name || '';
            form.email = val.email || '';
            form.role = val.role || 'craftsman';
            form.phone = val.phone || '';
            form.salary_type = val.salary_type || 'hybrid';
            form.base_salary = val.base_salary ?? 2000000;
            form.piece_rate = val.piece_rate ?? 20000;
            form.is_active = val.is_active !== undefined ? val.is_active : true;
            form.password = '';
            form.clearErrors();

            // Atur status aktif komponen Pokok & Insentif
            if (val.salary_type === 'piece_rate') {
                hasBaseSalary.value = false;
                hasIncentive.value = true;
            } else if (val.salary_type === 'fixed_monthly') {
                hasBaseSalary.value = true;
                hasIncentive.value = false;
            } else {
                hasBaseSalary.value = true;
                hasIncentive.value = true;
            }
        }
    },
    { immediate: true }
);

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
        // Jangan biarkan keduanya mati, alihkan ke insentif
        hasIncentive.value = true;
        hasBaseSalary.value = false;
    } else {
        hasBaseSalary.value = !hasBaseSalary.value;
    }
    syncSalaryType();
};

const toggleIncentive = () => {
    if (hasIncentive.value && !hasBaseSalary.value) {
        // Jangan biarkan keduanya mati, alihkan ke pokok
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

// Estimasi kuantitas output aktual untuk perhitungan insentif
const activeOutputQuantity = computed(() => {
    if (!props.user) return 80;
    if (form.role === 'craftsman') {
        return props.user.performance?.pairs_completed || 85;
    }
    if (form.role === 'sales') {
        return props.user.performance?.monthly_deals || 12;
    }
    return 0;
});

const outputUnitLabel = computed(() => {
    if (form.role === 'sales') return 'deals';
    return 'pasang';
});

// Komponen 1: Gaji Pokok Bulanan
const calculatedBaseSalary = computed(() => {
    if (!hasBaseSalary.value) return 0;
    return parseFloat(form.base_salary) || 0;
});

// Komponen 2: Insentif Borongan / Performa
const calculatedIncentive = computed(() => {
    if (!hasIncentive.value) return 0;
    const rate = parseFloat(form.piece_rate) || 0;
    return rate * activeOutputQuantity.value;
});

// Komponen 3: Total Take-Home Pay (Gaji Pokok + Insentif Borongan)
const calculatedTotalPayout = computed(() => {
    return calculatedBaseSalary.value + calculatedIncentive.value;
});

const submit = () => {
    if (!props.user) return;
    syncSalaryType();

    form.put(`/users/${props.user.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <div
        v-if="show && user"
        class="fixed inset-0 z-50 flex items-center justify-end bg-black/60 backdrop-blur-xs transition-opacity"
        @click.self="emit('close')"
    >
        <!-- Slide-over Drawer -->
        <div class="bg-white h-full max-w-xl w-full p-6 md:p-8 overflow-y-auto shadow-2xl border-l border-[#e1e5d3] flex flex-col justify-between animate-in slide-in-from-right duration-300">
            <div>
                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-[#f0f1eb]">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-[#0e0f0c] text-[#c6ef4e] flex items-center justify-center shadow-xs">
                            <span class="material-symbols-outlined text-2xl">account_circle</span>
                        </div>
                        <div>
                            <h2 class="font-headline text-lg font-bold text-[#0e0f0c] tracking-tight">
                                Staff Detail & Payroll Configuration
                            </h2>
                            <p class="font-body text-xs text-[#868b7d]">
                                Atur profil, lihat riwayat kinerja, dan atur skema upah staf.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="emit('close')"
                        class="w-9 h-9 rounded-full bg-[#f4f4ed] hover:bg-[#e8e9e2] text-[#4c4546] hover:text-[#0e0f0c] flex items-center justify-center transition cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="mt-6 space-y-5">
                    <!-- SECTION 1: PROFIL & KONTAK -->
                    <div class="space-y-4">
                        <span class="font-label text-xs font-bold text-[#0e0f0c] uppercase tracking-wider block">
                            1. Data Profil & Akses
                        </span>

                        <!-- Nama Lengkap -->
                        <div>
                            <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1">
                                Nama Lengkap Staf <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-3.5 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] border border-transparent focus:border-[#0e0f0c] transition"
                            />
                            <p v-if="form.errors.name" class="text-rose-600 text-[11px] mt-1">{{ form.errors.name }}</p>
                        </div>

                        <!-- Email & Nomor Kontak -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1">
                                    Alamat Email <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="w-full px-3.5 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] border border-transparent focus:border-[#0e0f0c] transition"
                                />
                                <p v-if="form.errors.email" class="text-rose-600 text-[11px] mt-1">{{ form.errors.email }}</p>
                            </div>

                            <div>
                                <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1">
                                    Nomor Telepon / WA
                                </label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    placeholder="+62 812-xxxx-xxxx"
                                    class="w-full px-3.5 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] border border-transparent focus:border-[#0e0f0c] transition"
                                />
                                <p v-if="form.errors.phone" class="text-rose-600 text-[11px] mt-1">{{ form.errors.phone }}</p>
                            </div>
                        </div>

                        <!-- Role & Status Aktif -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1">
                                    Role Workshop <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    v-model="form.role"
                                    class="w-full px-3.5 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] border border-transparent focus:border-[#0e0f0c] transition"
                                >
                                    <option value="craftsman">Pengrajin (Craftsman)</option>
                                    <option value="sales">Sales Force</option>
                                    <option value="admin">Administrator</option>
                                </select>
                                <p v-if="form.errors.role" class="text-rose-600 text-[11px] mt-1">{{ form.errors.role }}</p>
                            </div>

                            <div>
                                <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1">
                                    Status Keaktifan
                                </label>
                                <div class="flex items-center gap-3 h-[42px]">
                                    <button
                                        type="button"
                                        @click="form.is_active = true"
                                        :class="[
                                            'flex-1 py-2 px-3 rounded-xl font-label text-xs font-semibold transition cursor-pointer',
                                            form.is_active ? 'bg-[#0e0f0c] text-[#c6ef4e] shadow-xs' : 'bg-[#f4f4ed] text-[#868b7d]'
                                        ]"
                                    >
                                        Aktif
                                    </button>
                                    <button
                                        type="button"
                                        @click="form.is_active = false"
                                        :class="[
                                            'flex-1 py-2 px-3 rounded-xl font-label text-xs font-semibold transition cursor-pointer',
                                            !form.is_active ? 'bg-rose-700 text-white shadow-xs' : 'bg-[#f4f4ed] text-[#868b7d]'
                                        ]"
                                    >
                                        Nonaktif
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Ganti Password Opsional -->
                        <div>
                            <label class="block font-label text-xs font-semibold text-[#0e0f0c] mb-1">
                                Ubah Password <span class="text-[#868b7d] font-normal">(kosongkan jika tidak diganti)</span>
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                placeholder="Masukkan password baru"
                                class="w-full px-3.5 py-2.5 bg-[#f4f4ed] rounded-xl text-xs md:text-sm text-[#0e0f0c] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] border border-transparent focus:border-[#0e0f0c] transition"
                            />
                            <p v-if="form.errors.password" class="text-rose-600 text-[11px] mt-1">{{ form.errors.password }}</p>
                        </div>
                    </div>

                    <!-- SECTION 2: RIWAYAT KINERJA & REKAPITULASI OUTPUT KERJA -->
                    <div class="pt-5 border-t border-[#f0f1eb] space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-label text-xs font-bold text-[#0e0f0c] uppercase tracking-wider block">
                                2. Riwayat Kinerja & Output Kerja
                            </span>
                            <span class="text-[10px] font-semibold text-[#506600] bg-[#c9f250]/40 px-2.5 py-0.5 rounded-full">
                                Data Aktual Bulan Ini
                            </span>
                        </div>

                        <div class="bg-[#f4f4ed] rounded-2xl p-4 border border-[#e1e5d3] space-y-3">
                            <!-- Performance Metrics for Craftsman -->
                            <div v-if="form.role === 'craftsman'" class="grid grid-cols-2 gap-3 text-xs">
                                <div class="bg-white rounded-xl p-3.5 border border-[#e1e5d3] shadow-2xs">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[#868b7d] text-[11px]">Sepatu Selesai QC</span>
                                        <span class="material-symbols-outlined text-base text-[#506600]">check_circle</span>
                                    </div>
                                    <span class="font-headline text-xl font-extrabold text-[#0e0f0c]">
                                        {{ user.performance?.pairs_completed || 85 }} Pasang
                                    </span>
                                    <span class="text-[10px] text-[#506600] block mt-1 font-semibold">
                                        Dasar perhitungan insentif
                                    </span>
                                </div>

                                <div class="bg-white rounded-xl p-3.5 border border-[#e1e5d3] shadow-2xs">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[#868b7d] text-[11px]">Limbah Kulit Terlapor</span>
                                        <span class="material-symbols-outlined text-base text-amber-500">warning</span>
                                    </div>
                                    <span class="font-headline text-xl font-extrabold text-amber-600">
                                        {{ user.performance?.waste_logged || 2 }} Pasang Cacat
                                    </span>
                                    <span class="text-[10px] text-[#868b7d] block mt-1">
                                        Toleransi standar &lt; 5%
                                    </span>
                                </div>
                            </div>

                            <!-- Performance Metrics for Sales -->
                            <div v-else-if="form.role === 'sales'" class="grid grid-cols-2 gap-3 text-xs">
                                <div class="bg-white rounded-xl p-3.5 border border-[#e1e5d3] shadow-2xs">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[#868b7d] text-[11px]">Deals Tertutup</span>
                                        <span class="material-symbols-outlined text-base text-[#506600]">handshake</span>
                                    </div>
                                    <span class="font-headline text-xl font-extrabold text-[#0e0f0c]">
                                        {{ user.performance?.monthly_deals || 12 }} Pesanan
                                    </span>
                                    <span class="text-[10px] text-[#506600] block mt-1 font-semibold">
                                        Dasar komisi closing
                                    </span>
                                </div>

                                <div class="bg-white rounded-xl p-3.5 border border-[#e1e5d3] shadow-2xs">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[#868b7d] text-[11px]">Akumulasi Omzet</span>
                                        <span class="material-symbols-outlined text-base text-[#0e0f0c]">payments</span>
                                    </div>
                                    <span class="font-headline text-base md:text-lg font-extrabold text-[#0e0f0c]">
                                        {{ formatRupiah(user.performance?.monthly_revenue || 0) }}
                                    </span>
                                    <span class="text-[10px] text-[#868b7d] block mt-1">
                                        Kontribusi transaksi riil
                                    </span>
                                </div>
                            </div>

                            <!-- Performance Metrics for Admin -->
                            <div v-else class="text-xs text-[#4c4546] bg-white p-3.5 rounded-xl border border-[#e1e5d3] shadow-2xs">
                                <span class="font-bold text-[#0e0f0c] block text-xs mb-1">Manajemen Operasional & Keuangan</span>
                                Memantau seluruh operasional perajin, log limbah kulit, stok lembaran kulit mentah, dan simulasi skenario OptimaBiz.
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: SKEMA KOMPENSASI & KALKULASI GAJI (PAYROLL) -->
                    <div class="pt-5 border-t border-[#f0f1eb] space-y-3.5">
                        <div class="flex items-center justify-between">
                            <span class="font-label text-xs font-bold text-[#0e0f0c] uppercase tracking-wider">
                                3. Skema Kompensasi & Upah
                            </span>
                            <span class="text-[11px] font-semibold text-[#506600] bg-[#c9f250]/40 px-2.5 py-0.5 rounded-full">
                                {{ hasBaseSalary && hasIncentive ? 'Pokok + Insentif' : (hasBaseSalary ? 'Gaji Pokok' : 'Insentif Borongan') }}
                            </span>
                        </div>

                        <!-- Pilihan Komponen Upah: Cukup Gaji Pokok & Insentif Saja -->
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

                        <!-- Input Fields berdasarkan Komponen yang Aktif -->
                        <div class="space-y-3">
                            <!-- Input 1: Gaji Pokok Bulanan -->
                            <div v-if="hasBaseSalary" class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <label class="block font-label text-xs font-semibold text-[#0e0f0c]">
                                        Nominal Gaji Pokok Bulanan (Rp / bulan)
                                    </label>
                                    <span class="text-[10px] text-[#868b7d]">Tunjangan dasar / kehadiran</span>
                                </div>
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
                                <p class="text-[11px] text-[#868b7d]">
                                    Nominal dasar: <strong class="text-[#0e0f0c]">{{ formatRupiah(form.base_salary) }}</strong> / bulan.
                                </p>
                                <p v-if="form.errors.base_salary" class="text-rose-600 text-[11px] mt-1">{{ form.errors.base_salary }}</p>
                            </div>

                            <!-- Input 2: Tarif Upah Borongan / Insentif -->
                            <div v-if="hasIncentive" class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <label class="block font-label text-xs font-semibold text-[#0e0f0c]">
                                        {{ form.role === 'sales' ? 'Insentif per Deal Penjualan (Rp / deal)' : 'Tarif Insentif Borongan (Rp / pasang jadi)' }}
                                    </label>
                                    <span class="text-[10px] text-[#868b7d]">Dihitung dari output kerja</span>
                                </div>
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
                                <p class="text-[11px] text-[#868b7d]">
                                    Tarif output: <strong class="text-[#0e0f0c]">{{ formatRupiah(form.piece_rate) }}</strong> per {{ outputUnitLabel }} selesai.
                                </p>
                                <p v-if="form.errors.piece_rate" class="text-rose-600 text-[11px] mt-1">{{ form.errors.piece_rate }}</p>
                            </div>
                        </div>

                        <!-- LIVE BENTO CARD: KALKULASI TOTAL PAYOUT -->
                        <div class="mt-4 p-4 rounded-2xl bg-[#0e0f0c] text-white shadow-md space-y-3">
                            <!-- Header Card -->
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
                                            Dihitung otomatis dari output kerja di atas
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
                                <!-- 1. Komponen Gaji Pokok Bulanan -->
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

                                <!-- 2. Komponen Insentif Borongan / Performa -->
                                <div class="flex items-center justify-between py-1.5 px-3 rounded-xl" :class="hasIncentive ? 'bg-white/5' : 'opacity-40'">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full" :class="hasIncentive ? 'bg-[#c6ef4e]' : 'bg-neutral-600'"></span>
                                        <div>
                                            <span class="text-neutral-300 font-medium text-xs">Insentif Output Kerja:</span>
                                            <span class="text-[10px] text-neutral-400 block">
                                                {{ hasIncentive ? `${activeOutputQuantity} ${outputUnitLabel} (riwayat di atas) × ${formatRupiah(form.piece_rate)}` : 'Tidak diaktifkan' }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="font-headline text-xs md:text-sm font-bold" :class="hasIncentive ? 'text-[#c6ef4e]' : 'text-neutral-500'">
                                        + {{ formatRupiah(calculatedIncentive) }}
                                    </span>
                                </div>

                                <!-- 3. Grand Total Take-Home Pay -->
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

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#f0f1eb]">
                        <button
                            type="button"
                            @click="emit('close')"
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
                            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Konfigurasi Payroll' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
