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
const canAccessUsers = computed(() => user.value?.role === 'superadmin');
const canAccessSales = computed(() => ['superadmin', 'admin', 'sales'].includes(user.value?.role));
const canAccessInventory = computed(() => true); // Superadmin, Admin, Sales, Craftsman
const canAccessCategories = computed(() => ['superadmin', 'admin'].includes(user.value?.role));
const canAccessSimulator = computed(() => user.value?.role === 'superadmin');

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
    <!-- Spacer di Desktop agar konten utama tetap sejajar dan tidak tertutup sidebar yang fixed -->
    <div class="hidden md:block w-[72px] shrink-0" aria-hidden="true"></div>

    <!-- Navigation Container: Fixed Bottom Navigation Bar on Mobile (< md), Fixed Left Sidebar Rail on Desktop (>= md) -->
    <aside class="fixed bottom-0 left-0 right-0 z-40 w-full max-w-full overflow-x-hidden md:overflow-y-auto bg-[#0e0f0c]/95 backdrop-blur-md border-t border-[#262822] px-2 py-1.5 md:py-4 md:px-2.5 flex flex-row md:flex-col items-center justify-around md:justify-between shadow-2xl md:shadow-xl flex-shrink-0 md:top-5 md:left-5 md:bottom-auto md:right-auto md:h-[calc(100vh-2.5rem)] md:w-[72px] md:bg-[#0e0f0c] md:rounded-[24px] md:border-0">
        <!-- Logo Brand OptimaBiz (Desktop only) -->
        <Link href="/dashboard" class="hidden md:flex group relative items-center justify-center w-11 h-11" title="OptimaBiz Leathercraft">
            <svg class="w-9 h-9 transition-transform duration-300 group-hover:scale-105" fill="none" viewBox="0 0 44 44">
                <circle cx="22" cy="22" r="18" stroke="#22241e" stroke-linecap="round" stroke-width="4.5"></circle>
                <path d="M22 4C31.9411 4 40 12.0589 40 22C40 25.5 39 28.5 37.5 31" stroke="#c6ef4e" stroke-linecap="round" stroke-width="4.5"></path>
                <circle cx="12" cy="34" fill="#c6ef4e" r="3"></circle>
            </svg>
        </Link>

        <!-- Navigation Items (Horizontal tabs on Mobile, Vertical rail on Desktop) -->
        <nav aria-label="Navigasi Utama" class="flex flex-row md:flex-col items-center justify-around md:justify-center gap-1 md:gap-2 w-full md:w-auto my-0 md:my-auto">
            <!-- 1. Halaman Utama / Dashboard (Semua Peran) -->
            <Link
                href="/dashboard"
                :class="[
                    'flex flex-col items-center justify-center py-1 px-1 md:p-0 md:w-11 md:h-11 rounded-xl md:rounded-2xl transition-all cursor-pointer relative group flex-1 md:flex-initial',
                    active === 'dashboard'
                        ? 'text-[#c6ef4e] font-black md:bg-[#262822] md:shadow-inner'
                        : 'text-[#828876] hover:text-white md:hover:bg-[#1a1c17]'
                ]"
                title="Halaman Utama"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="text-[9px] font-bold mt-0.5 tracking-tight md:hidden">Beranda</span>
                <span v-if="active === 'dashboard'" class="w-1 h-1 rounded-full bg-[#c6ef4e] mt-0.5 md:hidden"></span>
            </Link>

            <!-- 2. Karyawan & Gaji (Super Admin) -->
            <Link
                v-if="canAccessUsers"
                href="/users"
                :class="[
                    'flex flex-col items-center justify-center py-1 px-1 md:p-0 md:w-11 md:h-11 rounded-xl md:rounded-2xl transition-all cursor-pointer relative group flex-1 md:flex-initial',
                    active === 'users'
                        ? 'text-[#c6ef4e] font-black md:bg-[#262822] md:shadow-inner'
                        : 'text-[#828876] hover:text-white md:hover:bg-[#1a1c17]'
                ]"
                title="Karyawan & Gaji"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="text-[9px] font-bold mt-0.5 tracking-tight md:hidden">Karyawan</span>
                <span v-if="active === 'users'" class="w-1 h-1 rounded-full bg-[#c6ef4e] mt-0.5 md:hidden"></span>
            </Link>

            <!-- 3. Penjualan & Kasir -->
            <Link
                v-if="canAccessSales"
                href="/sales"
                :class="[
                    'flex flex-col items-center justify-center py-1 px-1 md:p-0 md:w-11 md:h-11 rounded-xl md:rounded-2xl transition-all cursor-pointer relative group flex-1 md:flex-initial',
                    active === 'sales'
                        ? 'text-[#c6ef4e] font-black md:bg-[#262822] md:shadow-inner'
                        : 'text-[#828876] hover:text-white md:hover:bg-[#1a1c17]'
                ]"
                title="Penjualan & Kasir"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="text-[9px] font-bold mt-0.5 tracking-tight md:hidden">Penjualan</span>
                <span v-if="active === 'sales'" class="w-1 h-1 rounded-full bg-[#c6ef4e] mt-0.5 md:hidden"></span>
            </Link>

            <!-- 4. Stok Barang & Bahan -->
            <Link
                v-if="canAccessInventory"
                href="/inventory"
                :class="[
                    'flex flex-col items-center justify-center py-1 px-1 md:p-0 md:w-11 md:h-11 rounded-xl md:rounded-2xl transition-all cursor-pointer relative group flex-1 md:flex-initial',
                    active === 'inventory'
                        ? 'text-[#c6ef4e] font-black md:bg-[#262822] md:shadow-inner'
                        : 'text-[#828876] hover:text-white md:hover:bg-[#1a1c17]'
                ]"
                title="Stok Sepatu & Bahan"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="text-[9px] font-bold mt-0.5 tracking-tight md:hidden">Stok</span>
                <span v-if="active === 'inventory'" class="w-1 h-1 rounded-full bg-[#c6ef4e] mt-0.5 md:hidden"></span>
            </Link>

            <!-- 5. Kategori Produk (Super Admin & Admin) -->
            <Link
                v-if="canAccessCategories"
                href="/categories"
                :class="[
                    'flex flex-col items-center justify-center py-1 px-1 md:p-0 md:w-11 md:h-11 rounded-xl md:rounded-2xl transition-all cursor-pointer relative group flex-1 md:flex-initial',
                    active === 'categories'
                        ? 'text-[#c6ef4e] font-black md:bg-[#262822] md:shadow-inner'
                        : 'text-[#828876] hover:text-white md:hover:bg-[#1a1c17]'
                ]"
                title="Kategori Produk & Bahan"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="text-[9px] font-bold mt-0.5 tracking-tight md:hidden">Kategori</span>
                <span v-if="active === 'categories'" class="w-1 h-1 rounded-full bg-[#c6ef4e] mt-0.5 md:hidden"></span>
            </Link>

            <!-- 6. Audit Kebocoran Laba (Super Admin) -->
            <Link
                v-if="canAccessSimulator"
                href="/simulator"
                :class="[
                    'flex flex-col items-center justify-center py-1 px-1 md:p-0 md:w-11 md:h-11 rounded-xl md:rounded-2xl transition-all cursor-pointer relative group flex-1 md:flex-initial',
                    active === 'simulator'
                        ? 'text-[#c6ef4e] font-black md:bg-[#262822] md:shadow-inner'
                        : 'text-[#828876] hover:text-white md:hover:bg-[#1a1c17]'
                ]"
                title="Audit Kebocoran Laba"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="text-[9px] font-bold mt-0.5 tracking-tight md:hidden">Audit</span>
                <span v-if="active === 'simulator'" class="w-1 h-1 rounded-full bg-[#c6ef4e] mt-0.5 md:hidden"></span>
            </Link>

            <!-- Tombol Keluar / Logout di Mobile Nav Tab -->
            <button
                type="button"
                @click="handleLogout"
                class="flex flex-col items-center justify-center py-1 px-1 rounded-xl text-[#828876] hover:text-rose-400 transition-all cursor-pointer flex-1 md:hidden"
                title="Keluar dari Sesi"
            >
                <span class="material-symbols-outlined text-[20px] shrink-0">logout</span>
                <span class="text-[9px] font-bold mt-0.5 tracking-tight">Keluar</span>
            </button>
        </nav>

        <!-- Desktop Bottom User Profile & Logout Action -->
        <div class="hidden md:flex flex-col items-center gap-2 pt-2 border-t border-[#262822] w-full">
            <div
                v-if="user"
                :title="`${user.name} (${user.role?.toUpperCase()})`"
                class="group relative flex flex-col items-center"
            >
                <div
                    :class="[
                        'w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold shadow-xs select-none border',
                        user.role === 'superadmin'
                            ? 'bg-[#0e0f0c] text-[#c6ef4e] border-[#c6ef4e]'
                            : user.role === 'admin'
                            ? 'bg-[#1a1c18] text-[#c6ef4e] border-[#a2c833]'
                            : user.role === 'sales'
                            ? 'bg-[#c9f250]/40 text-[#c6ef4e] border-[#c6ef4e]/40'
                            : 'bg-[#262822] text-neutral-200 border-neutral-700'
                    ]"
                >
                    {{ getInitials(user.name) }}
                </div>
            </div>

            <!-- Desktop Logout Button -->
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
