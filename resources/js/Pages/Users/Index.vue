<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import SidebarNav from '@/Components/SidebarNav.vue';
import StaffMetricsBento from './Partials/StaffMetricsBento.vue';
import RoleFilterTabs from './Partials/RoleFilterTabs.vue';
import UserTable from './Partials/UserTable.vue';
import AddUserModal from './Partials/AddUserModal.vue';
import UserDetailModal from './Partials/UserDetailModal.vue';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    metrics: {
        type: Object,
        default: () => ({
            total_active_staff: 0,
            total_sales: 0,
            total_sales_deals: 0,
            total_sales_revenue: 0,
            total_craftsmen: 0,
            total_craftsmen_pairs: 0,
            monthly_max_capacity: 500,
            capacity_utilization: 0,
            total_monthly_payroll_est: 0,
        }),
    },
    counts: {
        type: Object,
        default: () => ({
            all: 0,
            admin: 0,
            sales: 0,
            craftsman: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            role: 'all',
            status: 'all',
            search: '',
            sort: 'name_asc',
        }),
    },
});

// Role Tab State
const activeRole = ref(props.filters.role || 'all');

// Modals & Drawer State
const showAddModal = ref(false);
const showDetailModal = ref(false);
const selectedUser = ref(null);

// Delete Confirmation State
const showDeleteConfirm = ref(false);
const userToDelete = ref(null);
const isDeleting = ref(false);

const handleSelectRole = (role) => {
    activeRole.value = role;
};

const handleOpenDetail = (user) => {
    selectedUser.value = user;
    showDetailModal.value = true;
};

const handleConfirmDelete = (user) => {
    userToDelete.value = user;
    showDeleteConfirm.value = true;
};

const executeDelete = () => {
    if (!userToDelete.value) return;
    isDeleting.value = true;

    router.delete(`/users/${userToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleting.value = false;
            showDeleteConfirm.value = false;
            userToDelete.value = null;
        },
        onError: () => {
            isDeleting.value = false;
        },
    });
};
</script>

<template>
    <Head title="Karyawan & Gaji · OptimaBiz" />

    <div class="min-h-screen w-full max-w-full overflow-x-hidden bg-[#eaecdf] p-2.5 md:p-5 pb-24 md:pb-5 flex flex-col md:flex-row gap-5 font-sans">
        <!-- Sidebar Navigation Rail -->
        <SidebarNav active="users" />

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col gap-4 md:gap-5 min-w-0 max-w-full">
            <!-- Top Header Section -->
            <div class="bg-white/90 backdrop-blur-md rounded-2xl sm:rounded-[24px] p-3.5 sm:p-5 border border-[#dce0cd] flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 shadow-sm">
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-2xl font-black text-[#0e0f0c] tracking-tight truncate">
                        Karyawan & Tukang
                    </h1>
                    <p class="text-xs text-[#737868] mt-0.5 font-medium leading-snug">
                        Kelola staf toko dan tukang sepatu, atur gaji tetap atau upah borongan
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                    <!-- Export CSV Button -->
                    <a
                        href="/users/export"
                        download
                        class="flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl sm:rounded-full bg-white text-[#0e0f0c] shadow-xs hover:bg-[#e3e3dc] transition-all cursor-pointer text-xs font-semibold border border-[#e1e5d3] flex-1 sm:flex-initial"
                        title="Unduh rekap data karyawan & gaji"
                    >
                        <span class="material-symbols-outlined text-[16px]">file_download</span>
                        <span>Unduh CSV</span>
                    </a>

                    <!-- + Add New Staff Button -->
                    <button
                        type="button"
                        @click="showAddModal = true"
                        class="flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl sm:rounded-full bg-[#0e0f0c] text-white hover:bg-[#20221c] shadow-md transition-all cursor-pointer text-xs font-bold flex-1 sm:flex-initial whitespace-nowrap"
                    >
                        <span class="material-symbols-outlined text-[16px] text-[#c6ef4e]">person_add</span>
                        <span>+ Tambah Karyawan</span>
                    </button>
                </div>
            </div>

            <!-- Top Metrics Row (3 Bento Cards) -->
            <StaffMetricsBento
                :metrics="metrics"
                :counts="counts"
            />

            <!-- Filter Tabs & Staff Table Bento Card -->
            <div class="flex flex-col gap-4">
                <!-- Role Filter Tabs [All] [Admins] [Sales] [Craftsmen] -->
                <RoleFilterTabs
                    :activeRole="activeRole"
                    :counts="counts"
                    @selectRole="handleSelectRole"
                />

                <!-- Main Table Card -->
                <UserTable
                    :users="users"
                    :activeRole="activeRole"
                    @openDetail="handleOpenDetail"
                    @confirmDelete="handleConfirmDelete"
                />
            </div>
        </main>

        <!-- Slide-over Drawer / Modal for Staff Detail & Payroll Configuration -->
        <UserDetailModal
            :show="showDetailModal"
            :user="selectedUser"
            @close="showDetailModal = false"
        />

        <!-- Add Staff Modal -->
        <AddUserModal
            :show="showAddModal"
            @close="showAddModal = false"
        />

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteConfirm && userToDelete"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
            @click.self="showDeleteConfirm = false"
        >
            <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 text-center border border-[#e1e5d3] animate-in fade-in zoom-in-95 duration-200">
                <div class="w-12 h-12 rounded-full bg-[#ffdad6] text-[#ba1a1a] flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-2xl">warning</span>
                </div>
                <h3 class="font-headline text-base font-bold text-[#0e0f0c]">Konfirmasi Hapus Staf</h3>
                <p class="font-body text-xs text-[#868b7d] mt-1.5">
                    Apakah Anda yakin ingin menghapus akun staf <strong class="text-[#0e0f0c]">{{ userToDelete.name }}</strong>? Data payroll dan historis akan ikut terhapus.
                </p>

                <div class="flex items-center justify-center gap-3 mt-5 pt-3 border-t border-[#f0f1eb]">
                    <button
                        type="button"
                        @click="showDeleteConfirm = false"
                        class="px-4 py-2 rounded-full bg-[#f4f4ed] hover:bg-[#e8e9e2] text-[#0e0f0c] font-label text-xs font-semibold transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="executeDelete"
                        :disabled="isDeleting"
                        class="px-5 py-2 rounded-full bg-[#ba1a1a] hover:bg-[#93000a] text-white font-label text-xs font-semibold transition cursor-pointer disabled:opacity-50 flex items-center gap-1.5"
                    >
                        <span v-if="isDeleting" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
