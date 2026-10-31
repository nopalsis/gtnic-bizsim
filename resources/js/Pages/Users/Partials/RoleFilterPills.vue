<script setup>
defineProps({
    activeRole: {
        type: String,
        default: 'all',
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
});

const emit = defineEmits(['selectRole']);

const selectRole = (role) => {
    emit('selectRole', role);
};
</script>

<template>
    <div class="flex items-center justify-between gap-4 overflow-x-auto no-scrollbar py-1">
        <div class="flex items-center gap-2">
            <!-- Semua Peran -->
            <button
                type="button"
                @click="selectRole('all')"
                :class="[
                    'filter-chip px-4 py-2 rounded-full font-label text-xs tracking-tight transition-all cursor-pointer flex items-center gap-1.5',
                    activeRole === 'all'
                        ? 'bg-[#0e0f0c] text-white shadow-sm font-semibold'
                        : 'bg-white text-[#4c4546] hover:text-[#0e0f0c] border border-[#e1e5d3] shadow-sm font-medium'
                ]"
            >
                <span>Semua Peran</span>
                <span :class="['text-[11px] px-1.5 py-0.5 rounded-full', activeRole === 'all' ? 'bg-[#262822] text-[#c6ef4e]' : 'bg-[#f4f4ed] text-[#4c4546]']">
                    {{ counts.all ?? 0 }}
                </span>
            </button>

            <!-- Administrator -->
            <button
                type="button"
                @click="selectRole('Administrator')"
                :class="[
                    'filter-chip px-4 py-2 rounded-full font-label text-xs tracking-tight transition-all cursor-pointer flex items-center gap-1.5',
                    activeRole === 'Administrator'
                        ? 'bg-[#0e0f0c] text-white shadow-sm font-semibold'
                        : 'bg-white text-[#4c4546] hover:text-[#0e0f0c] border border-[#e1e5d3] shadow-sm font-medium'
                ]"
            >
                <span>Administrator</span>
                <span :class="['text-[11px] px-1.5 py-0.5 rounded-full', activeRole === 'Administrator' ? 'bg-[#262822] text-[#c6ef4e]' : 'bg-[#f4f4ed] text-[#4c4546]']">
                    {{ counts.administrator ?? 0 }}
                </span>
            </button>

            <!-- Manager -->
            <button
                type="button"
                @click="selectRole('Manager')"
                :class="[
                    'filter-chip px-4 py-2 rounded-full font-label text-xs tracking-tight transition-all cursor-pointer flex items-center gap-1.5',
                    activeRole === 'Manager'
                        ? 'bg-[#0e0f0c] text-white shadow-sm font-semibold'
                        : 'bg-white text-[#4c4546] hover:text-[#0e0f0c] border border-[#e1e5d3] shadow-sm font-medium'
                ]"
            >
                <span>Manager</span>
                <span :class="['text-[11px] px-1.5 py-0.5 rounded-full', activeRole === 'Manager' ? 'bg-[#262822] text-[#c6ef4e]' : 'bg-[#f4f4ed] text-[#4c4546]']">
                    {{ counts.manager ?? 0 }}
                </span>
            </button>

            <!-- Operational Staff -->
            <button
                type="button"
                @click="selectRole('Operational Staff')"
                :class="[
                    'filter-chip px-4 py-2 rounded-full font-label text-xs tracking-tight transition-all cursor-pointer flex items-center gap-1.5',
                    activeRole === 'Operational Staff'
                        ? 'bg-[#0e0f0c] text-white shadow-sm font-semibold'
                        : 'bg-white text-[#4c4546] hover:text-[#0e0f0c] border border-[#e1e5d3] shadow-sm font-medium'
                ]"
            >
                <span>Operational Staff</span>
                <span :class="['text-[11px] px-1.5 py-0.5 rounded-full', activeRole === 'Operational Staff' ? 'bg-[#262822] text-[#c6ef4e]' : 'bg-[#f4f4ed] text-[#4c4546]']">
                    {{ counts.operational ?? 0 }}
                </span>
            </button>

            <!-- Contractor -->
            <button
                type="button"
                @click="selectRole('Contractor')"
                :class="[
                    'filter-chip px-4 py-2 rounded-full font-label text-xs tracking-tight transition-all cursor-pointer flex items-center gap-1.5',
                    activeRole === 'Contractor'
                        ? 'bg-[#0e0f0c] text-white shadow-sm font-semibold'
                        : 'bg-white text-[#4c4546] hover:text-[#0e0f0c] border border-[#e1e5d3] shadow-sm font-medium'
                ]"
            >
                <span>Contractor</span>
                <span :class="['text-[11px] px-1.5 py-0.5 rounded-full', activeRole === 'Contractor' ? 'bg-[#262822] text-[#c6ef4e]' : 'bg-[#f4f4ed] text-[#4c4546]']">
                    {{ counts.contractor ?? 0 }}
                </span>
            </button>
        </div>
    </div>
</template>

