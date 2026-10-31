<script setup>
const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    user: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'edit']);

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
</script>

<template>
    <div
        v-if="show && user"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity"
        @click.self="emit('close')"
    >
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 md:p-7 overflow-hidden border border-[#e1e5d3] animate-in fade-in zoom-in-95 duration-200">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b border-[#f0f1eb]">
                <h3 class="font-headline text-base font-bold text-[#0e0f0c]">Detail Profil Staf</h3>
                <button
                    type="button"
                    @click="emit('close')"
                    class="w-8 h-8 rounded-full bg-[#f4f4ed] hover:bg-[#e8e9e2] text-[#4c4546] hover:text-[#0e0f0c] flex items-center justify-center transition cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <!-- Profile Summary Card -->
            <div class="flex flex-col items-center text-center mt-5">
                <div class="relative w-20 h-20 rounded-full overflow-hidden bg-[#eeeee7] shadow-sm flex items-center justify-center mb-3">
                    <img
                        v-if="user.avatar_url"
                        :src="user.avatar_url"
                        :alt="user.name"
                        class="w-full h-full object-cover"
                    />
                    <span
                        v-else
                        :class="[
                            'w-full h-full flex items-center justify-center text-xl font-bold font-headline',
                            user.role === 'Administrator' ? 'bg-[#0e0f0c] text-[#c6ef4e]' : 'bg-[#e8e9e2] text-[#0e0f0c]'
                        ]"
                    >
                        {{ getInitials(user.name) }}
                    </span>
                </div>

                <h4 class="font-headline text-lg font-bold text-[#0e0f0c]">{{ user.name }}</h4>
                <p class="font-body text-xs text-[#868b7d] mt-0.5">{{ user.email }}</p>

                <!-- Badges (Role & Status) -->
                <div class="flex items-center gap-2 mt-3">
                    <span
                        v-if="user.role === 'Administrator'"
                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[#0e0f0c] text-white font-label text-xs font-bold"
                    >
                        <span class="material-symbols-outlined text-xs text-[#c6ef4e]">admin_panel_settings</span>
                        Administrator
                    </span>
                    <span
                        v-else-if="user.role === 'Manager'"
                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[#eeeee7] text-[#0e0f0c] font-label text-xs font-semibold"
                    >
                        <span class="material-symbols-outlined text-xs text-[#4c4546]">supervisor_account</span>
                        Manager
                    </span>
                    <span
                        v-else-if="user.role === 'Contractor'"
                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[#eeeee7] text-[#0e0f0c] font-label text-xs font-semibold"
                    >
                        <span class="material-symbols-outlined text-xs text-[#868b7d]">handyman</span>
                        Contractor
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[#eeeee7] text-[#4c4546] font-label text-xs font-medium"
                    >
                        <span class="material-symbols-outlined text-xs text-[#868b7d]">badge</span>
                        Operational Staff
                    </span>

                    <span
                        v-if="user.status === 'active'"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#c9f250]/40 text-[#3b4d00] font-label text-xs font-bold"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-[#506600]"></span>
                        Aktif
                    </span>
                    <span
                        v-else-if="user.status === 'on_leave'"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#e8e9e2] text-[#4c4546] font-label text-xs font-semibold"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-[#7e7576]"></span>
                        On Leave
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#ffdad6] text-[#ba1a1a] font-label text-xs font-bold"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-[#ba1a1a]"></span>
                        Nonaktif
                    </span>
                </div>
            </div>

            <!-- Detailed Attributes List -->
            <div class="mt-6 space-y-3 bg-[#f4f4ed]/70 rounded-2xl p-4 border border-[#e1e5d3]">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-body text-[#868b7d]">Nomor Telepon:</span>
                    <div class="flex items-center gap-2">
                        <span class="font-body font-semibold text-[#0e0f0c]">{{ user.phone || '-' }}</span>
                        <a
                            v-if="user.phone"
                            :href="'https://wa.me/' + getCleanPhone(user.phone)"
                            target="_blank"
                            class="px-2 py-0.5 rounded-md bg-[#25D366]/20 text-[#128C7E] hover:bg-[#25D366]/30 font-semibold text-[11px] transition"
                            title="Chat WhatsApp"
                        >
                            WA
                        </a>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <span class="font-body text-[#868b7d]">Akses Sistem:</span>
                    <span class="font-body font-semibold text-[#0e0f0c]">
                        {{ user.role === 'Administrator' ? 'Penuh (Full Access)' : (user.role === 'Manager' ? 'Approval & Laporan' : 'Operasional Harian') }}
                    </span>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <span class="font-body text-[#868b7d]">Terdaftar Sejak:</span>
                    <span class="font-body font-semibold text-[#0e0f0c]">{{ user.created_at || 'Baru Saja' }}</span>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-between gap-3 mt-6 pt-4 border-t border-[#f0f1eb]">
                <button
                    type="button"
                    @click="emit('close')"
                    class="px-4 py-2 rounded-full bg-[#f4f4ed] hover:bg-[#e8e9e2] text-[#0e0f0c] font-label text-xs font-semibold transition cursor-pointer"
                >
                    Tutup
                </button>
                <button
                    type="button"
                    @click="emit('edit', user)"
                    class="px-5 py-2 rounded-full bg-[#0e0f0c] hover:bg-[#20221c] text-white font-label text-xs font-semibold shadow-sm transition cursor-pointer flex items-center gap-1.5"
                >
                    <span class="material-symbols-outlined text-sm">edit</span>
                    <span>Edit Profil</span>
                </button>
            </div>
        </div>
    </div>
</template>

