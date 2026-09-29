<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const counter = ref(0);
const features = [
    {
        title: 'Laravel 13',
        description: 'Backend framework modern dan tangguh dengan arsitektur PHP 8.4.',
        badge: 'Backend',
        badgeColor: 'bg-red-500/10 text-red-400 border-red-500/20',
        icon: '⚡',
    },
    {
        title: 'Inertia.js v2',
        description: 'Menghubungkan Laravel backend dengan Vue frontend tanpa perlu membangun REST API secara manual.',
        badge: 'Bridge',
        badgeColor: 'bg-purple-500/10 text-purple-400 border-purple-500/20',
        icon: '🔄',
    },
    {
        title: 'Vue 3 & Vite',
        description: 'UI reaktif dengan Composition API, performa instan berkat Vite Hot Module Replacement (HMR).',
        badge: 'Frontend',
        badgeColor: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        icon: '💚',
    },
    {
        title: 'MySQL (Laragon)',
        description: 'Database relasional terhubung ke database "gtnic" bawaan Laragon.',
        badge: 'Database',
        badgeColor: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        icon: '🗄️',
    },
];
</script>

<template>
    <Head title="Welcome to GTNIC" />

    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-between selection:bg-rose-500 selection:text-white">
        <!-- Background Glow Effects -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute -top-40 -left-40 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl"></div>
            <div class="absolute top-1/3 -right-40 w-96 h-96 bg-emerald-600/15 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 left-1/3 w-96 h-96 bg-rose-600/15 rounded-full blur-3xl"></div>
        </div>

        <!-- Header / Navbar -->
        <header class="relative z-10 border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md">
            <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-500 via-purple-500 to-indigo-500 p-0.5 shadow-lg shadow-purple-500/20">
                        <div class="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center font-bold text-lg text-transparent bg-clip-text bg-gradient-to-r from-rose-400 to-indigo-400">
                            G
                        </div>
                    </div>
                    <div>
                        <span class="font-bold text-lg tracking-tight text-white">GTNIC</span>
                        <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 border border-slate-700">Inertia + Vue</span>
                    </div>
                </div>

                <div class="flex items-center gap-4 text-xs font-medium text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Laravel v{{ laravelVersion }}
                    </span>
                    <span class="hidden sm:inline text-slate-600">•</span>
                    <span class="hidden sm:inline">PHP v{{ phpVersion }}</span>
                </div>
            </div>
        </header>

        <!-- Main Content Hero -->
        <main class="relative z-10 max-w-6xl mx-auto px-6 py-12 flex-1 flex flex-col justify-center items-center text-center">
            <!-- Pill Tag -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900 border border-slate-800 text-xs font-medium text-slate-300 mb-8 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                Inisialisasi Project Berhasil
            </div>

            <!-- Title -->
            <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight max-w-3xl leading-tight">
                Laravel <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-400 via-purple-400 to-indigo-400">Inertia.js</span> & <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300">Vue 3</span>
            </h1>

            <p class="mt-5 text-base sm:text-lg text-slate-400 max-w-2xl leading-relaxed">
                Project berhasil dikonfigurasi dengan arsitektur Monolith modern berbasis JavaScript (Vue 3 Composition API), Inertia.js, Vite, dan database MySQL di Laragon.
            </p>

            <!-- Interactive Reactivity Demo Card -->
            <div class="mt-8 p-6 bg-slate-900/90 border border-slate-800 rounded-2xl shadow-xl max-w-md w-full backdrop-blur-sm">
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold mb-3">Uji Reaktivitas Vue</p>
                <div class="flex items-center justify-center gap-4">
                    <button
                        type="button"
                        @click="counter--"
                        class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 transition flex items-center justify-center text-lg font-bold border border-slate-700 cursor-pointer"
                        title="Kurang"
                    >
                        -
                    </button>
                    <div class="text-3xl font-black tabular-nums text-white min-w-[3rem]">
                        {{ counter }}
                    </div>
                    <button
                        type="button"
                        @click="counter++"
                        class="w-10 h-10 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-95 transition flex items-center justify-center text-lg font-bold text-white shadow-lg shadow-indigo-600/30 border border-indigo-500 cursor-pointer"
                        title="Tambah"
                    >
                        +
                    </button>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Klik tombol di atas untuk memastikan state Vue 3 berjalan lancar.</p>
            </div>

            <!-- Stack Grid -->
            <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-4 w-full text-left">
                <div
                    v-for="(feature, idx) in features"
                    :key="idx"
                    class="p-5 rounded-xl bg-slate-900/50 border border-slate-800/80 hover:border-slate-700 transition hover:bg-slate-900/80 group"
                >
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-2xl">{{ feature.icon }}</span>
                        <span :class="['text-[11px] px-2.5 py-0.5 rounded-full border font-medium', feature.badgeColor]">
                            {{ feature.badge }}
                        </span>
                    </div>
                    <h3 class="text-base font-semibold text-white group-hover:text-indigo-300 transition">
                        {{ feature.title }}
                    </h3>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                        {{ feature.description }}
                    </p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 border-t border-slate-800/80 py-6 text-center text-xs text-slate-500">
            <div class="max-w-6xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p>GTNIC • Dijalankan di Laragon local environment</p>
                <p>Edit halaman ini di <code class="px-1.5 py-0.5 bg-slate-900 border border-slate-800 rounded text-slate-300">resources/js/Pages/Welcome.vue</code></p>
            </div>
        </footer>
    </div>
</template>
