<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    active: {
        type: String,
        default: 'dashboard',
    },
});
</script>

<template>
    <aside class="w-full md:w-[72px] bg-[#0e0f0c] rounded-[24px] py-5 px-3 flex flex-row md:flex-col items-center justify-between shadow-xl flex-shrink-0 z-20 md:sticky md:top-4 md:self-start md:h-[calc(100vh-2rem)]">
        <!-- Logo Brand -->
        <Link href="/dashboard" class="group relative flex items-center justify-center w-11 h-11" title="GTNIC BizSim">
            <svg class="w-9 h-9 transition-transform duration-300 group-hover:scale-105" fill="none" viewBox="0 0 44 44">
                <circle cx="22" cy="22" r="18" stroke="#22241e" stroke-linecap="round" stroke-width="4.5"></circle>
                <path d="M22 4C31.9411 4 40 12.0589 40 22C40 25.5 39 28.5 37.5 31" stroke="#c6ef4e" stroke-linecap="round" stroke-width="4.5"></path>
                <circle cx="12" cy="34" fill="#c6ef4e" r="3"></circle>
            </svg>
        </Link>

        <!-- Navigation Rail -->
        <nav aria-label="Navigasi Utama" class="flex flex-row md:flex-col items-center gap-2 my-auto">
            <!-- Dashboard Finansial -->
            <Link
                href="/dashboard"
                :class="[
                    'w-11 h-11 rounded-2xl flex items-center justify-center transition-all cursor-pointer',
                    active === 'dashboard' ? 'bg-[#262822] text-[#c6ef4e] shadow-inner' : 'text-[#828876] hover:text-white hover:bg-[#1a1c17]'
                ]"
                title="Ringkasan Finansial"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </Link>

            <!-- Transaksi & Kasir (Future placeholder) -->
            <button
                type="button"
                :class="[
                    'w-11 h-11 rounded-2xl flex items-center justify-center transition-all cursor-pointer',
                    active === 'pos' ? 'bg-[#262822] text-[#c6ef4e]' : 'text-[#828876] hover:text-white hover:bg-[#1a1c17]'
                ]"
                title="Kasir & Transaksi POS"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </button>

            <!-- Inventaris & Stok -->
            <button
                type="button"
                :class="[
                    'w-11 h-11 rounded-2xl flex items-center justify-center transition-all cursor-pointer',
                    active === 'inventory' ? 'bg-[#262822] text-[#c6ef4e]' : 'text-[#828876] hover:text-white hover:bg-[#1a1c17]'
                ]"
                title="Inventaris & Bahan Baku"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </button>
        </nav>

        <!-- Bottom Status Dot -->
        <div class="hidden md:flex flex-col items-center gap-1.5" title="Sistem Aktif">
            <span class="w-2.5 h-2.5 rounded-full bg-[#c6ef4e] animate-pulse"></span>
        </div>
    </aside>
</template>
