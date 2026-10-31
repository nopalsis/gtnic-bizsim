<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import SidebarNav from '@/Components/SidebarNav.vue';
import RoleFilterPills from './Partials/RoleFilterPills.vue';
import UserTable from './Partials/UserTable.vue';
import AddUserModal from './Partials/AddUserModal.vue';
import EditUserModal from './Partials/EditUserModal.vue';
import UserDetailModal from './Partials/UserDetailModal.vue';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    counts: {
        type: Object,
        default: () => ({
            all: 0,
            administrator: 0,
            manager: 0,
            operational: 0,
            contractor: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            role: 'all',
            search: '',
            status: 'all',
            sort: 'name_asc',
        }),
    },
});

// Active Role Filter
const activeRole = ref(props.filters.role || 'all');

// Modals State
const showAddModal = ref(false);
const showEditModal = ref(false);
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

const handleOpenEdit = (user) => {
    selectedUser.value = user;
    showDetailModal.value = false;
    showEditModal.value = true;
};

const handleConfirmDelete = (user) => {
    userToDelete.value = user;
    showDeleteConfirm.value = true;
};

const executeDelete = () => {
    if (!userToDelete.value) return;
    isDeleting.value = true;

    router.delete(`/users/${userToDelete.value.id}`, {
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
    <Head title="Team & User Management · OptimaBiz" />

    <div class="min-h-screen w-full bg-[#eaecdf] p-2 md:p-5 flex flex-col md:flex-row gap-5 font-sans">
        <!-- Sidebar Navigation Rail -->
        <SidebarNav active="users" />

        <!-- Main Dashboard / Staff Management Area -->
        <main class="flex-1 flex flex-col gap-6 min-w-0">
            <div class="flex flex-col w-full">
                <!-- Top Header Section -->
                <div class="flex flex-col gap-5 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h1 class="font-headline text-2xl md:text-[26px] font-bold text-[#0e0f0c] tracking-tight">
                                Team & User Management
                            </h1>
                            <p class="font-body text-xs md:text-sm text-[#4c4546] mt-1">
                                Kelola daftar staf, peran akses, dan kontak operasional workshop.
                            </p>
                        </div>

                        <!-- Header Action Buttons -->
                        <div class="flex items-center gap-3">
                            <!-- Export CSV -->
                            <a
                                href="/users/export"
                                download
                                class="flex items-center gap-2 px-5 py-2.5 rounded-full bg-white text-[#0e0f0c] shadow-sm hover:bg-[#e3e3dc] transition-all cursor-pointer font-label text-xs font-semibold border border-[#e1e5d3]"
                                title="Unduh berkas CSV daftar staf"
                            >
                                <span class="material-symbols-outlined text-base">file_download</span>
                                <span>Export CSV</span>
                            </a>

                            <!-- Add Staff Button -->
                            <button
                                type="button"
                                @click="showAddModal = true"
                                class="flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#0e0f0c] text-white hover:bg-[#20221c] shadow-md transition-all cursor-pointer font-label text-xs font-semibold"
                            >
                                <span class="material-symbols-outlined text-base text-[#c6ef4e]">person_add</span>
                                <span>+ Add Staff</span>
                            </button>
                        </div>
                    </div>

                    <!-- Role Filter Pills -->
                    <RoleFilterPills
                        :activeRole="activeRole"
                        :counts="counts"
                        @selectRole="handleSelectRole"
                    />
                </div>

                <!-- Main Staff Directory Table Card -->
                <UserTable
                    :users="users"
                    :activeRole="activeRole"
                    @openDetail="handleOpenDetail"
                    @openEdit="handleOpenEdit"
                    @confirmDelete="handleConfirmDelete"
                />
            </div>
        </main>

        <!-- Modals -->
        <AddUserModal
            :show="showAddModal"
            @close="showAddModal = false"
        />

        <EditUserModal
            :show="showEditModal"
            :user="selectedUser"
            @close="showEditModal = false"
        />

        <UserDetailModal
            :show="showDetailModal"
            :user="selectedUser"
            @close="showDetailModal = false"
            @edit="handleOpenEdit"
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
                    Apakah Anda yakin ingin menghapus akun staf <span class="font-semibold text-[#0e0f0c]">{{ userToDelete.name }}</span>? Tindakan ini tidak dapat dibatalkan.
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

