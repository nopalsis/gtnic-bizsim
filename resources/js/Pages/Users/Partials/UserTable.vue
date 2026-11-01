<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    activeRole: {
        type: String,
        default: 'all',
    },
});

const emit = defineEmits(['openDetail', 'confirmDelete']);

// State Search & Filter
const searchQuery = ref('');
const statusFilter = ref('all'); // 'all' | 'active' | 'inactive'
const sortOrder = ref('name_asc'); // 'name_asc' | 'name_desc' | 'role' | 'salary_desc'

// Pagination State
const currentPage = ref(1);
const itemsPerPage = ref(8);

// Helpers
const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};

const getInitials = (name) => {
    if (!name) return '??';
    const parts = name.trim().split(' ');
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

const getCleanPhone = (phone) => {
    if (!phone) return '';
    return phone.replace(/[^0-9]/g, '');
};

// Filtered & Sorted Users
const filteredUsers = computed(() => {
    let result = [...props.users];

    // Filter Peran
    if (props.activeRole && props.activeRole !== 'all') {
        result = result.filter((u) => u.role === props.activeRole);
    }

    // Filter Status Aktif
    if (statusFilter.value === 'active') {
        result = result.filter((u) => u.is_active === true);
    } else if (statusFilter.value === 'inactive') {
        result = result.filter((u) => u.is_active === false);
    }

    // Filter Pencarian Teks
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase().trim();
        result = result.filter(
            (u) =>
                u.name?.toLowerCase().includes(q) ||
                u.email?.toLowerCase().includes(q) ||
                u.phone?.toLowerCase().includes(q)
        );
    }

    // Pengurutan
    if (sortOrder.value === 'name_asc') {
        result.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
    } else if (sortOrder.value === 'name_desc') {
        result.sort((a, b) => (b.name || '').localeCompare(a.name || ''));
    } else if (sortOrder.value === 'role') {
        result.sort((a, b) => (a.role || '').localeCompare(b.role || ''));
    } else if (sortOrder.value === 'salary_desc') {
        result.sort((a, b) => (b.performance?.estimated_payout || 0) - (a.performance?.estimated_payout || 0));
    }

    return result;
});

// Paginasi
const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredUsers.value.length / itemsPerPage.value));
});

const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredUsers.value.slice(start, start + itemsPerPage.value);
});

const showingStart = computed(() => {
    if (filteredUsers.value.length === 0) return 0;
    return (currentPage.value - 1) * itemsPerPage.value + 1;
});

const showingEnd = computed(() => {
    return Math.min(currentPage.value * itemsPerPage.value, filteredUsers.value.length);
});

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

// Toggle status langsung dari tabel
const toggleUserStatus = (user) => {
    router.patch(`/users/${user.id}/toggle-status`, {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="flex flex-col bg-white rounded-[28px] shadow-xs p-5 md:p-6 overflow-hidden border border-[#e1e5d3]">
        <!-- Table Toolbar -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pb-5 border-b border-[#f0f1eb]">
            <!-- Search Input -->
            <div class="relative flex-1 max-w-md">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-lg text-[#868b7d] select-none pointer-events-none">
                    search
                </span>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari nama staf, email, nomor WA, atau bank..."
                    class="w-full pl-10 pr-9 py-2.5 bg-[#f4f4ed] rounded-full text-xs md:text-sm text-[#0e0f0c] placeholder:text-[#868b7d] focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#0e0f0c] transition-all"
                    @input="currentPage = 1"
                />
                <button
                    v-if="searchQuery"
                    @click="searchQuery = ''; currentPage = 1"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-700 text-xs p-1"
                    title="Hapus pencarian"
                >
                    ✕
                </button>
            </div>

            <!-- Toolbar Controls & Counter -->
            <div class="flex items-center gap-3">
                <span class="px-3.5 py-1.5 rounded-full bg-[#eeeee7] text-[#0e0f0c] font-label text-xs font-semibold select-none">
                    Total {{ filteredUsers.length }} Staf
                </span>

                <!-- Status Filter Selector -->
                <select
                    v-model="statusFilter"
                    class="h-9 px-3.5 bg-[#f4f4ed] rounded-full text-xs font-semibold text-[#0e0f0c] border-transparent focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] cursor-pointer"
                    @change="currentPage = 1"
                >
                    <option value="all">Semua Status</option>
                    <option value="active">Hanya Aktif</option>
                    <option value="inactive">Hanya Nonaktif</option>
                </select>

                <!-- Sorting Selector -->
                <select
                    v-model="sortOrder"
                    class="h-9 px-3.5 bg-[#f4f4ed] rounded-full text-xs font-semibold text-[#0e0f0c] border-transparent focus:outline-none focus:ring-1 focus:ring-[#0e0f0c] cursor-pointer"
                    @change="currentPage = 1"
                >
                    <option value="name_asc">Nama (A - Z)</option>
                    <option value="name_desc">Nama (Z - A)</option>
                    <option value="role">Role Staf</option>
                </select>
            </div>
        </div>

        <!-- Staff Table -->
        <div class="overflow-x-auto -mx-5 md:-mx-6 px-5 md:px-6 mt-2">
            <table class="w-full text-left align-middle border-collapse">
                <thead>
                    <tr class="bg-[#f4f4ed]/80 text-[#4c4546] rounded-2xl select-none">
                        <th class="py-3 px-4 font-label text-xs font-semibold rounded-l-2xl">Nama & Email</th>
                        <th class="py-3 px-3 font-label text-xs font-semibold">Role</th>
                        <th class="py-3 px-3 font-label text-xs font-semibold">Kontak / WA</th>
                        <th class="py-3 px-3 font-label text-xs font-semibold">Performa Bulan Ini</th>
                        <th class="py-3 px-3 font-label text-xs font-semibold text-center">Status</th>
                        <th class="py-3 px-4 font-label text-xs font-semibold text-right rounded-r-2xl">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y-0 text-[#0e0f0c]">
                    <tr
                        v-for="user in paginatedUsers"
                        :key="user.id"
                        class="hover:bg-[#f4f4ed]/50 transition-colors group border-b border-[#f4f4ed] last:border-b-0"
                    >
                        <!-- Kolom 1: Nama & Email -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div
                                    :class="[
                                        'w-10 h-10 rounded-full flex items-center justify-center font-headline text-xs font-bold flex-shrink-0 select-none shadow-xs',
                                        user.role === 'admin'
                                            ? 'bg-[#0e0f0c] text-[#c6ef4e]'
                                            : (user.role === 'sales' ? 'bg-[#c9f250]/40 text-[#3b4d00]' : 'bg-[#eeeee7] text-[#0e0f0c]')
                                    ]"
                                >
                                    {{ getInitials(user.name) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-headline text-sm font-semibold text-[#0e0f0c] tracking-tight truncate max-w-[180px]">
                                        {{ user.name }}
                                    </div>
                                    <div class="font-body text-xs text-[#868b7d] truncate max-w-[180px]">
                                        {{ user.email }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Kolom 2: Peran -->
                        <td class="py-3.5 px-3 whitespace-nowrap">
                            <span
                                v-if="user.role === 'admin'"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#0e0f0c] text-white font-label text-[11px] font-bold shadow-xs"
                            >
                                <span class="material-symbols-outlined text-xs text-[#c6ef4e]">admin_panel_settings</span>
                                Admin
                            </span>

                            <span
                                v-else-if="user.role === 'sales'"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#c9f250]/40 text-[#3b4d00] font-label text-[11px] font-bold"
                            >
                                <span class="material-symbols-outlined text-xs text-[#506600]">point_of_sale</span>
                                Sales Force
                            </span>

                            <span
                                v-else
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#eeeee7] text-[#1a1c18] font-label text-[11px] font-semibold"
                            >
                                <span class="material-symbols-outlined text-xs text-[#868b7d]">precision_manufacturing</span>
                                Pengrajin
                            </span>
                        </td>

                        <!-- Kolom 3: Kontak & WA -->
                        <td class="py-3.5 px-3 font-body text-xs text-[#0e0f0c] whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <span>{{ user.phone || '-' }}</span>
                                <a
                                    v-if="user.phone"
                                    :href="'https://wa.me/' + getCleanPhone(user.phone)"
                                    target="_blank"
                                    class="p-1 rounded-full bg-[#25D366]/15 hover:bg-[#25D366]/30 text-[#128C7E] transition cursor-pointer"
                                    title="Kirim Pesan WhatsApp"
                                >
                                    <span class="material-symbols-outlined text-xs">chat</span>
                                </a>
                            </div>
                        </td>

                        <!-- Kolom 4: Performa Snippet -->
                        <td class="py-3.5 px-3 whitespace-nowrap">
                            <!-- Sales Snippet -->
                            <div v-if="user.role === 'sales'" class="flex flex-col">
                                <span class="font-headline text-xs font-bold text-[#0e0f0c]">
                                    {{ user.performance?.monthly_deals || 0 }} Deals
                                </span>
                                <span class="font-body text-[10px] text-[#506600] font-semibold">
                                    {{ formatRupiah(user.performance?.monthly_revenue || 0) }}
                                </span>
                            </div>

                            <!-- Craftsman Snippet -->
                            <div v-else-if="user.role === 'craftsman'" class="flex flex-col">
                                <span class="font-headline text-xs font-bold text-[#0e0f0c]">
                                    {{ user.performance?.pairs_completed || 0 }} Pasang Selesai
                                </span>
                                <span class="font-body text-[10px] text-amber-700">
                                    {{ user.performance?.waste_logged || 0 }} waste log
                                </span>
                            </div>

                            <!-- Admin Snippet -->
                            <div v-else class="text-xs font-body text-[#868b7d]">
                                Operasional & Sistem
                            </div>
                        </td>

                        <!-- Kolom 6: Status Aktif (Toggle) -->
                        <td class="py-3.5 px-3 text-center whitespace-nowrap">
                            <button
                                type="button"
                                @click="toggleUserStatus(user)"
                                :class="[
                                    'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-label text-[11px] font-bold transition-all cursor-pointer select-none',
                                    user.is_active
                                        ? 'bg-[#c9f250]/40 text-[#3b4d00] hover:bg-[#c9f250]/60'
                                        : 'bg-[#ffdad6] text-[#ba1a1a] hover:bg-[#ffdad6]/80'
                                ]"
                                :title="'Klik untuk ' + (user.is_active ? 'menonaktifkan' : 'mengaktifkan') + ' staf'"
                            >
                                <span
                                    :class="[
                                        'w-1.5 h-1.5 rounded-full',
                                        user.is_active ? 'bg-[#506600]' : 'bg-[#ba1a1a]'
                                    ]"
                                ></span>
                                <span>{{ user.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </button>
                        </td>

                        <!-- Kolom 7: Aksi -->
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <button
                                    type="button"
                                    @click="emit('openDetail', user)"
                                    class="px-3 py-1.5 rounded-full bg-[#0e0f0c] hover:bg-[#20221c] text-white font-label text-[11px] font-semibold transition cursor-pointer flex items-center gap-1 shadow-xs"
                                >
                                    <span class="material-symbols-outlined text-xs text-[#c6ef4e]">tune</span>
                                    <span>Payroll & Detail</span>
                                </button>

                                <button
                                    type="button"
                                    @click="emit('confirmDelete', user)"
                                    class="p-1.5 rounded-full hover:bg-[#ffdad6] text-[#4c4546] hover:text-[#ba1a1a] transition cursor-pointer"
                                    title="Hapus Staf"
                                >
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="filteredUsers.length === 0">
                        <td colspan="7" class="py-12 text-center text-[#868b7d]">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-4xl text-neutral-400">group_off</span>
                                <p class="font-body text-sm font-medium text-neutral-600">Tidak ada staf yang sesuai dengan kriteria filter.</p>
                                <button
                                    type="button"
                                    @click="searchQuery = ''; statusFilter = 'all'"
                                    class="text-xs text-[#0e0f0c] font-semibold underline mt-1 cursor-pointer"
                                >
                                    Reset Filter & Pencarian
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-5 mt-2 border-t border-[#f4f4ed]">
            <span class="font-body text-xs text-[#868b7d]">
                Menampilkan <span class="font-bold text-[#0e0f0c]">{{ showingStart }}-{{ showingEnd }}</span> dari
                <span class="font-bold text-[#0e0f0c]">{{ filteredUsers.length }}</span> staf
            </span>

            <div class="flex items-center gap-1.5">
                <button
                    type="button"
                    @click="goToPage(currentPage - 1)"
                    :disabled="currentPage <= 1"
                    class="px-3 py-1.5 rounded-full bg-[#f4f4ed] text-[#4c4546] hover:text-[#0e0f0c] font-label text-xs font-medium cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed transition"
                >
                    Sebelumnya
                </button>

                <button
                    v-for="page in totalPages"
                    :key="page"
                    type="button"
                    @click="goToPage(page)"
                    :class="[
                        'w-8 h-8 rounded-full font-label text-xs font-bold flex items-center justify-center transition cursor-pointer',
                        currentPage === page
                            ? 'bg-[#0e0f0c] text-white shadow-xs'
                            : 'bg-[#f4f4ed] text-[#0e0f0c] hover:bg-[#e8e9e2]'
                    ]"
                >
                    {{ page }}
                </button>

                <button
                    type="button"
                    @click="goToPage(currentPage + 1)"
                    :disabled="currentPage >= totalPages"
                    class="px-3 py-1.5 rounded-full bg-[#f4f4ed] text-[#0e0f0c] hover:bg-[#e8e9e2] font-label text-xs font-semibold cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed transition"
                >
                    Selanjutnya
                </button>
            </div>
        </div>
    </div>
</template>
