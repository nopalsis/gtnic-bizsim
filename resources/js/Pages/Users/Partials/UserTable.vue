<script setup>
import { ref, computed } from 'vue';

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

const emit = defineEmits(['openDetail', 'openEdit', 'confirmDelete']);

// Search & Filter State
const searchQuery = ref('');
const statusFilter = ref('all');
const sortOrder = ref('name_asc'); // 'name_asc' | 'name_desc' | 'id_desc'

// Pagination State
const currentPage = ref(1);
const itemsPerPage = ref(8);

// Helper for Initials
const getInitials = (name) => {
    if (!name) return '??';
    const parts = name.trim().split(' ');
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

// Filtered & Sorted Users
const filteredUsers = computed(() => {
    let result = [...props.users];

    // Role filter
    if (props.activeRole && props.activeRole !== 'all') {
        result = result.filter((u) => u.role === props.activeRole);
    }

    // Status filter
    if (statusFilter.value && statusFilter.value !== 'all') {
        result = result.filter((u) => u.status === statusFilter.value);
    }

    // Search query
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase().trim();
        result = result.filter(
            (u) =>
                u.name?.toLowerCase().includes(q) ||
                u.email?.toLowerCase().includes(q) ||
                u.phone?.toLowerCase().includes(q) ||
                u.role?.toLowerCase().includes(q)
        );
    }

    // Sorting
    if (sortOrder.value === 'name_asc') {
        result.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
    } else if (sortOrder.value === 'name_desc') {
        result.sort((a, b) => (b.name || '').localeCompare(a.name || ''));
    } else if (sortOrder.value === 'id_desc') {
        result.sort((a, b) => b.id - a.id);
    }

    return result;
});

// Paginated Users
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

const toggleSort = () => {
    if (sortOrder.value === 'name_asc') {
        sortOrder.value = 'name_desc';
    } else if (sortOrder.value === 'name_desc') {
        sortOrder.value = 'id_desc';
    } else {
        sortOrder.value = 'name_asc';
    }
    currentPage.value = 1;
};

const toggleStatusFilter = () => {
    const statuses = ['all', 'active', 'on_leave', 'inactive'];
    const nextIdx = (statuses.indexOf(statusFilter.value) + 1) % statuses.length;
    statusFilter.value = statuses[nextIdx];
    currentPage.value = 1;
};
</script>

<template>
    <div class="flex flex-col bg-white rounded-[28px] shadow-sm p-5 md:p-6 overflow-hidden border border-[#e1e5d3]">
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
                    placeholder="Cari nama staf, email, atau kontak..."
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

                <!-- Filter Status Toggle Button -->
                <button
                    type="button"
                    @click="toggleStatusFilter"
                    :class="[
                        'h-9 px-3 rounded-full flex items-center gap-1 text-xs transition cursor-pointer select-none',
                        statusFilter !== 'all' ? 'bg-[#0e0f0c] text-white font-semibold' : 'bg-[#f4f4ed] text-[#0e0f0c] hover:bg-[#e8e9e2]'
                    ]"
                    :title="'Filter Status: ' + (statusFilter === 'all' ? 'Semua' : statusFilter)"
                >
                    <span class="material-symbols-outlined text-base">tune</span>
                    <span class="capitalize hidden md:inline">{{ statusFilter === 'all' ? 'Status' : statusFilter }}</span>
                </button>

                <!-- Sort Toggle Button -->
                <button
                    type="button"
                    @click="toggleSort"
                    class="w-9 h-9 rounded-full bg-[#f4f4ed] flex items-center justify-center text-[#0e0f0c] hover:bg-[#e8e9e2] transition cursor-pointer"
                    :title="'Urutan: ' + (sortOrder === 'name_asc' ? 'Nama A-Z' : (sortOrder === 'name_desc' ? 'Nama Z-A' : 'Terbaru'))"
                >
                    <span class="material-symbols-outlined text-base">sort_by_alpha</span>
                </button>
            </div>
        </div>

        <!-- Staff Directory Table -->
        <div class="overflow-x-auto -mx-5 md:-mx-6 px-5 md:px-6 mt-2">
            <table class="w-full text-left align-middle border-collapse">
                <thead>
                    <tr class="bg-[#f4f4ed]/80 text-[#4c4546] rounded-2xl select-none">
                        <th class="py-3 px-4 font-label text-xs font-semibold rounded-l-2xl">Nama & Email</th>
                        <th class="py-3 px-3 font-label text-xs font-semibold">Peran</th>
                        <th class="py-3 px-3 font-label text-xs font-semibold">Nomor Kontak</th>
                        <th class="py-3 px-3 font-label text-xs font-semibold">Status</th>
                        <th class="py-3 px-4 font-label text-xs font-semibold text-right rounded-r-2xl">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y-0 text-[#0e0f0c]">
                    <tr
                        v-for="user in paginatedUsers"
                        :key="user.id"
                        class="hover:bg-[#f4f4ed]/50 transition-colors group border-b border-[#f4f4ed] last:border-b-0"
                    >
                        <!-- Column 1: Nama & Email -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="relative w-10 h-10 rounded-full overflow-hidden bg-[#eeeee7] flex-shrink-0 flex items-center justify-center">
                                    <img
                                        v-if="user.avatar_url"
                                        :src="user.avatar_url"
                                        :alt="user.name"
                                        class="w-full h-full object-cover"
                                        loading="lazy"
                                    />
                                    <span
                                        v-else
                                        :class="[
                                            'w-full h-full flex items-center justify-center text-xs font-bold font-headline select-none',
                                            user.role === 'Administrator' ? 'bg-[#0e0f0c] text-[#c6ef4e]' : 'bg-[#e8e9e2] text-[#0e0f0c]'
                                        ]"
                                    >
                                        {{ getInitials(user.name) }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-headline text-sm text-[#0e0f0c] font-semibold tracking-tight truncate max-w-[200px] md:max-w-[280px]">
                                        {{ user.name }}
                                    </div>
                                    <div class="font-body text-xs text-[#868b7d] truncate max-w-[200px] md:max-w-[280px]">
                                        {{ user.email }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Column 2: Peran (Role) -->
                        <td class="py-3.5 px-3 whitespace-nowrap">
                            <!-- Role: Administrator -->
                            <span
                                v-if="user.role === 'Administrator'"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#0e0f0c] text-white font-label text-[11px] font-bold tracking-tight shadow-xs"
                            >
                                <span class="material-symbols-outlined text-xs text-[#c6ef4e]">admin_panel_settings</span>
                                Administrator
                            </span>

                            <!-- Role: Manager -->
                            <span
                                v-else-if="user.role === 'Manager'"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#eeeee7] text-[#0e0f0c] font-label text-[11px] font-semibold"
                            >
                                <span class="material-symbols-outlined text-xs text-[#4c4546]">supervisor_account</span>
                                Manager
                            </span>

                            <!-- Role: Contractor -->
                            <span
                                v-else-if="user.role === 'Contractor'"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#eeeee7] text-[#0e0f0c] font-label text-[11px] font-semibold"
                            >
                                <span class="material-symbols-outlined text-xs text-[#868b7d]">handyman</span>
                                Contractor
                            </span>

                            <!-- Role: Operational Staff (Default) -->
                            <span
                                v-else
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#eeeee7] text-[#4c4546] font-label text-[11px] font-medium"
                            >
                                <span class="material-symbols-outlined text-xs text-[#868b7d]">badge</span>
                                Operational Staff
                            </span>
                        </td>

                        <!-- Column 3: Nomor Kontak -->
                        <td class="py-3.5 px-3 font-body text-xs text-[#0e0f0c] font-medium whitespace-nowrap">
                            {{ user.phone || '-' }}
                        </td>

                        <!-- Column 4: Status -->
                        <td class="py-3.5 px-3 whitespace-nowrap">
                            <!-- Status: Aktif -->
                            <span
                                v-if="user.status === 'active'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#c9f250]/40 text-[#3b4d00] font-label text-[11px] font-bold"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-[#506600]"></span>
                                Aktif
                            </span>

                            <!-- Status: On Leave -->
                            <span
                                v-else-if="user.status === 'on_leave'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#e8e9e2] text-[#4c4546] font-label text-[11px] font-semibold"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-[#7e7576]"></span>
                                On Leave
                            </span>

                            <!-- Status: Inactive -->
                            <span
                                v-else
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#ffdad6] text-[#ba1a1a] font-label text-[11px] font-bold"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-[#ba1a1a]"></span>
                                Nonaktif
                            </span>
                        </td>

                        <!-- Column 5: Aksi -->
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <button
                                    type="button"
                                    @click="emit('openDetail', user)"
                                    class="px-2.5 py-1 rounded-full bg-[#f4f4ed] hover:bg-[#e8e9e2] text-[#0e0f0c] font-label text-[11px] font-medium transition cursor-pointer"
                                >
                                    Detail
                                </button>
                                <button
                                    type="button"
                                    @click="emit('openEdit', user)"
                                    class="p-1.5 rounded-full hover:bg-[#e8e9e2] text-[#4c4546] hover:text-[#0e0f0c] transition cursor-pointer"
                                    title="Edit Staf"
                                >
                                    <span class="material-symbols-outlined text-sm">edit</span>
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
                        <td colspan="5" class="py-12 text-center text-[#868b7d]">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-4xl text-neutral-400">group_off</span>
                                <p class="font-body text-sm font-medium text-neutral-600">Tidak ada data staf yang cocok dengan kriteria pencarian.</p>
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

        <!-- Bottom Pagination Bar -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-5 mt-2 border-t border-[#f4f4ed]">
            <span class="font-body text-xs text-[#868b7d]">
                Showing <span class="font-bold text-[#0e0f0c]">{{ showingStart }}-{{ showingEnd }}</span> of
                <span class="font-bold text-[#0e0f0c]">{{ filteredUsers.length }}</span> staff
            </span>

            <div class="flex items-center gap-1.5">
                <!-- Previous Button -->
                <button
                    type="button"
                    @click="goToPage(currentPage - 1)"
                    :disabled="currentPage <= 1"
                    class="px-3 py-1.5 rounded-full bg-[#f4f4ed] text-[#4c4546] hover:text-[#0e0f0c] font-label text-xs font-medium cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed transition"
                >
                    Previous
                </button>

                <!-- Page Numbers -->
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

                <!-- Next Button -->
                <button
                    type="button"
                    @click="goToPage(currentPage + 1)"
                    :disabled="currentPage >= totalPages"
                    class="px-3 py-1.5 rounded-full bg-[#f4f4ed] text-[#0e0f0c] hover:bg-[#e8e9e2] font-label text-xs font-semibold cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed transition"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

