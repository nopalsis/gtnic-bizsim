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
            admin: 0,
            sales: 0,
            craftsman: 0,
        }),
    },
});

const emit = defineEmits(['selectRole']);
</script>

<template>
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
        <!-- Tab: All -->
        <button
            type="button"
            @click="emit('selectRole', 'all')"
            :class="[
                'px-4 py-2 rounded-full font-label text-xs tracking-tight transition-all cursor-pointer flex items-center gap-2 select-none',
                activeRole === 'all'
                    ? 'bg-[#0e0f0c] text-white font-bold shadow-xs'
                    : 'bg-white text-[#4c4546] hover:text-[#0e0f0c] border border-[#e1e5d3] font-medium'
            ]"
        >
            <span>Semua Role</span>
            <span
                :class="[
                    'text-[11px] px-2 py-0.5 rounded-full font-bold',
                    activeRole === 'all' ? 'bg-[#262822] text-[#c6ef4e]' : 'bg-[#f4f4ed] text-[#4c4546]'
                ]"
            >
                {{ counts.all }}
            </span>
        </button>

        <!-- Tab: Admins -->
        <button
            type="button"
            @click="emit('selectRole', 'admin')"
            :class="[
                'px-4 py-2 rounded-full font-label text-xs tracking-tight transition-all cursor-pointer flex items-center gap-2 select-none',
                activeRole === 'admin'
                    ? 'bg-[#0e0f0c] text-white font-bold shadow-xs'
                    : 'bg-white text-[#4c4546] hover:text-[#0e0f0c] border border-[#e1e5d3] font-medium'
            ]"
        >
            <span class="material-symbols-outlined text-xs" :class="activeRole === 'admin' ? 'text-[#c6ef4e]' : 'text-neutral-500'">admin_panel_settings</span>
            <span>Admins</span>
            <span
                :class="[
                    'text-[11px] px-2 py-0.5 rounded-full font-bold',
                    activeRole === 'admin' ? 'bg-[#262822] text-[#c6ef4e]' : 'bg-[#f4f4ed] text-[#4c4546]'
                ]"
            >
                {{ counts.admin }}
            </span>
        </button>

        <!-- Tab: Sales Force -->
        <button
            type="button"
            @click="emit('selectRole', 'sales')"
            :class="[
                'px-4 py-2 rounded-full font-label text-xs tracking-tight transition-all cursor-pointer flex items-center gap-2 select-none',
                activeRole === 'sales'
                    ? 'bg-[#0e0f0c] text-white font-bold shadow-xs'
                    : 'bg-white text-[#4c4546] hover:text-[#0e0f0c] border border-[#e1e5d3] font-medium'
            ]"
        >
            <span class="material-symbols-outlined text-xs" :class="activeRole === 'sales' ? 'text-[#c6ef4e]' : 'text-neutral-500'">point_of_sale</span>
            <span>Sales Force</span>
            <span
                :class="[
                    'text-[11px] px-2 py-0.5 rounded-full font-bold',
                    activeRole === 'sales' ? 'bg-[#262822] text-[#c6ef4e]' : 'bg-[#f4f4ed] text-[#4c4546]'
                ]"
            >
                {{ counts.sales }}
            </span>
        </button>

        <!-- Tab: Craftsmen -->
        <button
            type="button"
            @click="emit('selectRole', 'craftsman')"
            :class="[
                'px-4 py-2 rounded-full font-label text-xs tracking-tight transition-all cursor-pointer flex items-center gap-2 select-none',
                activeRole === 'craftsman'
                    ? 'bg-[#0e0f0c] text-white font-bold shadow-xs'
                    : 'bg-white text-[#4c4546] hover:text-[#0e0f0c] border border-[#e1e5d3] font-medium'
            ]"
        >
            <span class="material-symbols-outlined text-xs" :class="activeRole === 'craftsman' ? 'text-[#c6ef4e]' : 'text-neutral-500'">precision_manufacturing</span>
            <span>Pengrajin (Craftsmen)</span>
            <span
                :class="[
                    'text-[11px] px-2 py-0.5 rounded-full font-bold',
                    activeRole === 'craftsman' ? 'bg-[#262822] text-[#c6ef4e]' : 'bg-[#f4f4ed] text-[#4c4546]'
                ]"
            >
                {{ counts.craftsman }}
            </span>
        </button>
    </div>
</template>

