<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

defineProps({
    active: {
        type: String,
        default: 'dashboard',
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || null);

// Otorisasi Visibilitas Menu Berdasarkan Peran
const canAccessUsers = computed(() => user.value?.role === 'admin');
const canAccessSales = computed(() => user.value?.role === 'admin' || user.value?.role === 'sales');
const canAccessInventory = computed(() => true); // Admin, Sales, Craftsman
const canAccessSimulator = computed(() => user.value?.role === 'admin');

const getInitials = (name) => {
    if (!name) return '??';
    const parts = name.trim().split(' ');
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

const handleLogout = () => {
    if (confirm('Apakah Anda yakin ingin keluar dari sistem OptimaBiz?')) {
        router.post('/logout');
    }
};
</script>

<template>
    <aside class="w-full md:w-[72px] bg-[#0e0f0c] rounded-[24px] py-4 px-2.5 flex flex-row md:flex-col items-center justify-between shadow-xl flex-shrink-0 z-20 md:sticky md:top-4 md:self-start md:h-[calc(100vh-2rem)]">
        <!-- Logo Brand OptimaBiz -->
        <Link href="/dashboard" class="group relative flex items-center justify-center w-11 h-11" title="OptimaBiz Leathercraft">
            <svg class="w-9 h-9 transition-transform duration-300 group-hover:scale-105" fill="none" viewBox="0 0 44 44">
                <circle cx="22" cy="22" r="18" stroke="#22241e" stroke-linecap="round" stroke-width="4.5"></circle>
                <path d="M22 4C31.9411 4 40 12.0589 40 22C40 25.5 39 28.5 37.5 31" stroke="#c6ef4e" stroke-linecap="round" stroke-width="4.5"></path>
                <circle cx="12" cy="34" fill="#c6ef4e" r="3"></circle>
            </svg>
        </Link>

        <!-- Navigation Rail for Authorized Modules -->
        <nav aria-label="Navigasi Utama" class="flex flex-row md:flex-col items-center gap-2 my-auto">
            <!-- 1. Executive Dashboard (Semua Peran) -->
            <Link
                href="/dashboard"
                :class="[
                    'w-11 h-11 rounded-2xl flex items-center justify-center transition-all cursor-pointer relative group',
                    active === 'dashboard' ? 'bg-[#262822] text-[#c6ef4e] shadow-inner' : 'text-[#828876] hover:text-white hover:bg-[#1a1c17]'
                ]"
                title="Executive Dashboard"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="sr-only">Dashboard</span>
            </Link>

            <!-- 2. Team & User Management (Hanya Admin) -->
            <Link
                v-if="canAccessUsers"
                href="/users"
                :class="[
                    'w-11 h-11 rounded-2xl flex items-center justify-center transition-all cursor-pointer relative group',
                    active === 'users' ? 'bg-[#262822] text-[#c6ef4e] shadow-inner' : 'text-[#828876] hover:text-white hover:bg-[#1a1c17]'
                ]"
                title="Team & User Management (Admin Only)"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="sr-only">Users & Team</span>
            </Link>

            <!-- 3. Sales Intelligence & History (Admin & Sales) -->
            <Link
                v-if="canAccessSales"
                href="/sales"
                :class="[
                    'w-11 h-11 rounded-2xl flex items-center justify-center transition-all cursor-pointer relative group',
                    active === 'sales' ? 'bg-[#262822] text-[#c6ef4e] shadow-inner' : 'text-[#828876] hover:text-white hover:bg-[#1a1c17]'
                ]"
                title="Sales Intelligence & Negosiasi Deal Price"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="sr-only">Sales</span>
            </Link>

            <!-- 4. Inventory & Waste Tracker (Admin, Sales, Craftsman) -->
            <Link
                v-if="canAccessInventory"
                href="/inventory"
                :class="[
                    'w-11 h-11 rounded-2xl flex items-center justify-center transition-all cursor-pointer relative group',
                    active === 'inventory' ? 'bg-[#262822] text-[#c6ef4e] shadow-inner' : 'text-[#828876] hover:text-white hover:bg-[#1a1c17]'
                ]"
                title="Inventaris Stok & Waste Tracker"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="sr-only">Inventaris</span>
            </Link>

            <!-- 5. What-If Scenario Simulator (Hanya Admin) -->
            <Link
                v-if="canAccessSimulator"
                href="/simulator"
                :class="[
                    'w-11 h-11 rounded-2xl flex items-center justify-center transition-all cursor-pointer relative group',
                    active === 'simulator' ? 'bg-[#262822] text-[#c6ef4e] shadow-inner' : 'text-[#828876] hover:text-white hover:bg-[#1a1c17]'
                ]"
                title="What-If Scenario Simulator (Admin Only)"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="sr-only">Simulator</span>
            </Link>
        </nav>

        <!-- Bottom User Profile & Logout Action -->
        <div class="flex flex-col items-center gap-2 pt-2 border-t border-[#262822] w-full">
            <!-- User Avatar & Role Tooltip -->
            <div
                v-if="user"
                :title="`${user.name} (${user.role?.toUpperCase()})`"
                class="group relative flex flex-col items-center"
            >
                <div
                    :class="[
                        'w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold shadow-xs select-none border',
                        user.role === 'admin'
                            ? 'bg-[#0e0f0c] text-[#c6ef4e] border-[#c6ef4e]/60'
                            : user.role === 'sales'
                            ? 'bg-[#c9f250]/40 text-[#c6ef4e] border-[#c6ef4e]/40'
                            : 'bg-[#262822] text-neutral-200 border-neutral-700'
                    ]"
                >
                    {{ getInitials(user.name) }}
                </div>
            </div>

            <!-- Logout Button -->
            <button
                type="button"
                @click="handleLogout"
                class="w-9 h-9 rounded-xl text-[#828876] hover:text-rose-400 hover:bg-rose-950/40 flex items-center justify-center transition-all cursor-pointer"
                title="Keluar dari Sesi (Logout)"
            >
                <span class="material-symbols-outlined text-[19px]">logout</span>
            </button>
        </div>
    </aside>
</template>
