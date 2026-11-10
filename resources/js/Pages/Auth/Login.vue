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
    <Head title="Masuk · OptimaBiz" />

    <div class="min-h-screen min-h-[100dvh] w-full bg-[#eaecdf] flex items-center justify-center px-3.5 py-6 sm:p-6 md:p-8 font-sans relative overflow-x-hidden">
        <!-- Latar Belakang -->
        <div class="absolute inset-0 bg-grid-ambient pointer-events-none opacity-40"></div>
        <div class="absolute -top-32 -left-32 w-72 sm:w-96 h-72 sm:h-96 rounded-full bg-[#c6ef4e]/15 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-72 sm:w-96 h-72 sm:h-96 rounded-full bg-[#0e0f0c]/10 blur-3xl pointer-events-none"></div>

        <!-- Kartu Login -->
        <div class="w-full max-w-[440px] bg-white rounded-2xl sm:rounded-[32px] p-5 sm:p-8 md:p-9 shadow-lg border border-[#dce0cd] relative z-10 my-auto">
            <!-- Logo & Judul -->
            <div class="text-center mb-6 sm:mb-8">
                <!-- Ikon Logo -->
                <div class="inline-flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-[#0e0f0c] shadow-md mb-2.5 sm:mb-3 group transition-transform hover:scale-105 active:scale-95">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10" fill="none" viewBox="0 0 44 44">
                        <circle cx="22" cy="22" r="18" stroke="#22241e" stroke-linecap="round" stroke-width="4.5"></circle>
                        <path d="M22 4C31.9411 4 40 12.0589 40 22C40 25.5 39 28.5 37.5 31" stroke="#c6ef4e" stroke-linecap="round" stroke-width="4.5"></path>
                        <circle cx="12" cy="34" fill="#c6ef4e" r="3"></circle>
                    </svg>
                </div>

                <!-- Label Sederhana -->
                <div class="flex items-center justify-center gap-1.5 sm:gap-2 mb-1 px-2">
                    <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-[#c6ef4e] shrink-0"></span>
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#737868]">
                        UMKM SEPATU DAN SANDAL KULIT
                    </span>
                </div>

                <!-- Judul Utama -->
                <h1 class="text-xl sm:text-2xl md:text-[26px] font-extrabold text-[#0e0f0c] tracking-tight">
                    Masuk ke Sistem
                </h1>
            </div>

            <!-- Pesan Peringatan / Salah Sandi -->
            <div
                v-if="form.errors.email || form.errors.password"
                class="mb-4 sm:mb-5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-2.5 animate-in fade-in duration-200"
            >
                <span class="material-symbols-outlined text-[18px] text-rose-600 shrink-0 mt-0.5">error</span>
                <span class="font-medium leading-relaxed">{{ form.errors.email || form.errors.password }}</span>
            </div>

            <!-- Form Isian -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Kolom Email -->
                <div>
                    <label class="block text-xs font-semibold text-[#0e0f0c] mb-1.5">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[18px] text-[#737868] pointer-events-none select-none">
                            mail
                        </span>
                        <input
                            v-model="form.email"
                            type="email"
                            inputmode="email"
                            required
                            autocomplete="email"
                            autocapitalize="none"
                            autocorrect="off"
                            spellcheck="false"
                            placeholder="nama@email.com"
                            class="w-full pl-10 pr-4 py-3 sm:py-2.5 rounded-full bg-[#f4f4ed] text-[#0e0f0c] placeholder:text-[#868b7d] text-base sm:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0e0f0c]/20 focus:border-[#0e0f0c] transition"
                        />
                    </div>
                </div>

                <!-- Kolom Kata Sandi -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-[#0e0f0c]">
                            Kata Sandi
                        </label>
                    </div>

                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[18px] text-[#737868] pointer-events-none select-none">
                            lock
                        </span>
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="current-password"
                            placeholder="Ketik kata sandi"
                            class="w-full pl-10 pr-11 py-3 sm:py-2.5 rounded-full bg-[#f4f4ed] text-[#0e0f0c] placeholder:text-[#868b7d] text-base sm:text-sm border border-[#dce0cd] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0e0f0c]/20 focus:border-[#0e0f0c] transition"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-[#737868] hover:text-[#0e0f0c] active:bg-[#e1e5d3]/50 rounded-full transition cursor-pointer"
                            title="Tampilkan / Sembunyikan Sandi"
                            tabindex="-1"
                        >
                            <span class="material-symbols-outlined text-[19px]">
                                {{ showPassword ? 'visibility_off' : 'visibility' }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Ingat Saya & Lupa Sandi -->
                <div class="flex flex-row items-center justify-between text-xs pt-1 gap-2">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-[#0e0f0c] min-h-[32px]">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="w-4 h-4 rounded text-[#0e0f0c] focus:ring-0 border-[#dce0cd] cursor-pointer"
                        />
                        <span class="text-[#3a3d34] text-[11px] sm:text-xs">Ingat saya</span>
                    </label>

                </div>

                <!-- Tombol Masuk -->
                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3.5 sm:py-3 px-6 rounded-full bg-[#0e0f0c] text-white hover:bg-[#20221c] active:scale-[0.99] font-bold text-sm shadow-md transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2 group min-h-[46px]"
                    >
                        <span v-if="form.processing" class="material-symbols-outlined text-base animate-spin">
                            progress_activity
                        </span>
                        <span v-else class="material-symbols-outlined text-[18px] text-[#c6ef4e] group-hover:translate-x-0.5 transition-transform">
                            login
                        </span>
                        <span>{{ form.processing ? 'Memeriksa...' : 'Masuk' }}</span>
                    </button>
                </div>
            </form>

            <!-- Keterangan Bawah -->
            <div class="mt-6 sm:mt-8 pt-4 border-t border-[#f0f1eb] text-center">
                <p class="text-[10px] sm:text-[11px] text-[#737868] leading-relaxed">
                    Aplikasi Pencatatan & Pengelolaan Usaha Bengkel Sepatu Kulit
                </p>
            </div>
        </div>
    </div>
</template>
