<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk ke Sistem · OptimaBiz" />

    <div class="min-h-screen w-full bg-[#eaecdf] flex items-center justify-center p-4 md:p-6 font-sans relative overflow-hidden">
        <!-- Ambient Background Aesthetic Grid & Accents -->
        <div class="absolute inset-0 bg-grid-ambient pointer-events-none opacity-40"></div>
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-[#c6ef4e]/15 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-[#0e0f0c]/10 blur-3xl pointer-events-none"></div>

        <!-- Main Login Card Container -->
        <div class="w-full max-w-md bg-white rounded-[32px] p-6 sm:p-8 md:p-9 shadow-lg border border-[#dce0cd] relative z-10">
            <!-- Brand Logo & Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#0e0f0c] shadow-md mb-3 group transition-transform hover:scale-105">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 44 44">
                        <circle cx="22" cy="22" r="18" stroke="#22241e" stroke-linecap="round" stroke-width="4.5"></circle>
                        <path d="M22 4C31.9411 4 40 12.0589 40 22C40 25.5 39 28.5 37.5 31" stroke="#c6ef4e" stroke-linecap="round" stroke-width="4.5"></path>
                        <circle cx="12" cy="34" fill="#c6ef4e" r="3"></circle>
                    </svg>
                </div>

                <div class="flex items-center justify-center gap-2 mb-1">
                    <span class="w-2 h-2 rounded-full bg-[#c6ef4e]"></span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#737868]">
                        Sales Intelligence & Decision Support
                    </span>
                </div>

                <h1 class="text-2xl md:text-[26px] font-extrabold text-[#0e0f0c] tracking-tight">
                    OptimaBiz Portal
                </h1>
                <p class="text-xs text-[#737868] mt-1.5 max-w-xs mx-auto">
                    Silakan masukkan email dan kata sandi akun Anda untuk mengakses sistem
                </p>
            </div>

            <!-- Error Banner -->
            <div
                v-if="form.errors.email || form.errors.password"
                class="mb-5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-2.5"
            >
                <span class="material-symbols-outlined text-[18px] text-rose-600 shrink-0 mt-0.5">error</span>
                <span class="font-medium leading-relaxed">{{ form.errors.email || form.errors.password }}</span>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Email Input -->
                <div>
                    <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[18px] text-[#737868] pointer-events-none">
                            mail
                        </span>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="nama@optimabiz.id"
                            class="w-full pl-10 pr-4 py-2.5 rounded-full bg-[#f4f4ed] text-[#0e0f0c] placeholder:text-[#868b7d] text-xs md:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition"
                        />
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-[#0e0f0c]">
                            Kata Sandi
                        </label>
                    </div>

                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[18px] text-[#737868] pointer-events-none">
                            lock
                        </span>
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan kata sandi"
                            class="w-full pl-10 pr-10 py-2.5 rounded-full bg-[#f4f4ed] text-[#0e0f0c] placeholder:text-[#868b7d] text-xs md:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] transition"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[#737868] hover:text-[#0e0f0c] p-1 cursor-pointer transition"
                            title="Tampilkan / Sembunyikan Sandi"
                        >
                            <span class="material-symbols-outlined text-[18px]">
                                {{ showPassword ? 'visibility_off' : 'visibility' }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Assistance -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-[#0e0f0c]">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="w-4 h-4 rounded text-[#0e0f0c] focus:ring-0 border-[#dce0cd] cursor-pointer"
                        />
                        <span class="text-[#3a3d34]">Ingat sesi saya</span>
                    </label>

                    <span class="text-[11px] text-[#737868]">
                        Lupa sandi? Hubungi Admin
                    </span>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-6 rounded-full bg-[#0e0f0c] text-white hover:bg-[#20221c] font-bold text-xs md:text-sm shadow-md transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2 group"
                    >
                        <span v-if="form.processing" class="material-symbols-outlined text-base animate-spin">
                            progress_activity
                        </span>
                        <span v-else class="material-symbols-outlined text-[18px] text-[#c6ef4e] group-hover:translate-x-0.5 transition-transform">
                            login
                        </span>
                        <span>{{ form.processing ? 'Memverifikasi Kredensial...' : 'Masuk ke Sistem' }}</span>
                    </button>
                </div>
            </form>

            <!-- Footer Note -->
            <div class="mt-8 pt-4 border-t border-[#f0f1eb] text-center">
                <p class="text-[11px] text-[#737868] leading-relaxed">
                    Sistem Manajemen & Kontrol Operasional Bengkel Sepatu Kulit
                </p>
            </div>
        </div>
    </div>
</template>
