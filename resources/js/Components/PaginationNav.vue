<script setup>
import { computed } from 'vue';

const props = defineProps({
    currentPage: {
        type: Number,
        required: true,
    },
    totalPages: {
        type: Number,
        required: true,
    },
    variant: {
        type: String,
        default: 'default', // 'default' | 'pill' | 'compact'
    },
    prevLabel: {
        type: String,
        default: 'Sebelumnya',
    },
    nextLabel: {
        type: String,
        default: 'Selanjutnya',
    },
});

const emit = defineEmits(['change']);

const goTo = (page) => {
    if (typeof page !== 'number') return;
    if (page >= 1 && page <= props.totalPages && page !== props.currentPage) {
        emit('change', page);
    }
};

/**
 * Bangun daftar nomor halaman dengan pemotongan elipsis (`...`).
 * - Mobile: maksimal 5 token (misal [1, '...', 5, '...', 9] atau [1, 2, '...', 9])
 *   agar tombol Sebelumnya & Selanjutnya tetap ringkas di tengah dan tidak terdesak ke pinggir.
 * - Desktop: maksimal 7 token (misal [1, '...', 4, 5, 6, '...', 9]).
 */
const buildPages = (current, total, isMobile) => {
    const safeTotal = Math.max(1, total);
    const c = Math.min(Math.max(1, current), safeTotal);

    if (isMobile) {
        if (safeTotal <= 4) {
            return Array.from({ length: safeTotal }, (_, i) => i + 1);
        }
        if (c <= 2) {
            return [1, 2, '...', safeTotal];
        }
        if (c >= safeTotal - 1) {
            return [1, '...', safeTotal - 1, safeTotal];
        }
        return [1, '...', c, '...', safeTotal];
    }

    if (safeTotal <= 7) {
        return Array.from({ length: safeTotal }, (_, i) => i + 1);
    }
    if (c <= 3) {
        return [1, 2, 3, 4, '...', safeTotal];
    }
    if (c >= safeTotal - 2) {
        return [1, '...', safeTotal - 3, safeTotal - 2, safeTotal - 1, safeTotal];
    }
    return [1, '...', c - 1, c, c + 1, '...', safeTotal];
};

const mobilePages = computed(() => buildPages(props.currentPage, props.totalPages, true));
const desktopPages = computed(() => buildPages(props.currentPage, props.totalPages, false));

const navButtonClasses = computed(() => {
    if (props.variant === 'compact') {
        return 'px-2 sm:px-2.5 py-1 rounded-lg border border-[#dce0cd] font-bold text-[10px] transition-all disabled:opacity-30 disabled:cursor-not-allowed hover:bg-neutral-100 cursor-pointer whitespace-nowrap shrink-0 inline-flex items-center gap-0.5';
    }
    if (props.variant === 'pill') {
        return 'px-2 sm:px-3 py-1 sm:py-1.5 rounded-full bg-[#f2f4ec] text-[#0e0f0c] hover:bg-[#eaecdf] text-[10px] sm:text-xs font-semibold transition disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer whitespace-nowrap shrink-0 inline-flex items-center gap-0.5';
    }
    return 'px-2 sm:px-3 py-1 sm:py-1.5 rounded-xl border border-[#dce0cd] font-bold text-[10px] sm:text-[11px] transition-all disabled:opacity-30 disabled:cursor-not-allowed hover:bg-neutral-100 cursor-pointer whitespace-nowrap shrink-0 inline-flex items-center gap-0.5';
});

const getPageButtonClasses = (page) => {
    const isActive = page === props.currentPage;

    if (props.variant === 'compact') {
        return [
            'min-w-[24px] h-6 px-1 rounded-md font-bold text-[10px] flex items-center justify-center transition-all cursor-pointer tabular-nums',
            isActive ? 'bg-[#0e0f0c] text-[#c6ef4e]' : 'text-[#737868] hover:bg-neutral-100',
        ];
    }
    if (props.variant === 'pill') {
        return [
            'min-w-[24px] sm:min-w-[30px] h-6 sm:h-[30px] px-1 rounded-full text-[10px] sm:text-xs font-bold flex items-center justify-center transition cursor-pointer tabular-nums',
            isActive ? 'bg-[#0e0f0c] text-white shadow-xs' : 'bg-[#f2f4ec] text-[#0e0f0c] hover:bg-[#eaecdf]',
        ];
    }
    return [
        'min-w-[24px] sm:min-w-[32px] h-6 sm:h-8 px-1 rounded-lg sm:rounded-xl font-black text-[10px] sm:text-xs flex items-center justify-center transition-all cursor-pointer tabular-nums',
        isActive ? 'bg-[#0e0f0c] text-[#c6ef4e] shadow-xs' : 'text-[#737868] hover:bg-neutral-100',
    ];
};

const ellipsisClasses = computed(() => {
    if (props.variant === 'compact') {
        return 'px-0.5 text-[10px] font-bold text-[#737868] select-none';
    }
    return 'px-0.5 sm:px-1 text-[10px] sm:text-xs font-bold text-[#737868] select-none';
});
</script>

<template>
    <nav
        aria-label="Navigasi Halaman"
        class="flex flex-wrap items-center justify-center gap-1 sm:gap-1.5 max-w-full"
    >
        <!-- Tombol Sebelumnya -->
        <button
            type="button"
            @click="goTo(currentPage - 1)"
            :disabled="currentPage <= 1"
            :class="navButtonClasses"
        >
            <span aria-hidden="true" class="sm:hidden">‹</span>
            <span aria-hidden="true" class="hidden sm:inline">←</span>
            <span>{{ prevLabel }}</span>
        </button>

        <!-- Nomor Halaman Mobile (Ringkas dengan ...) -->
        <div class="flex sm:hidden items-center gap-0.5">
            <template v-for="(item, idx) in mobilePages" :key="item === '...' ? 'm-dots-' + idx : 'm-pg-' + item">
                <span v-if="item === '...'" :class="ellipsisClasses">...</span>
                <button
                    v-else
                    type="button"
                    @click="goTo(item)"
                    :aria-current="currentPage === item ? 'page' : undefined"
                    :class="getPageButtonClasses(item)"
                >
                    {{ item }}
                </button>
            </template>
        </div>

        <!-- Nomor Halaman Desktop (Lebih lengkap dengan ...) -->
        <div class="hidden sm:flex items-center gap-1">
            <template v-for="(item, idx) in desktopPages" :key="item === '...' ? 'd-dots-' + idx : 'd-pg-' + item">
                <span v-if="item === '...'" :class="ellipsisClasses">...</span>
                <button
                    v-else
                    type="button"
                    @click="goTo(item)"
                    :aria-current="currentPage === item ? 'page' : undefined"
                    :class="getPageButtonClasses(item)"
                >
                    {{ item }}
                </button>
            </template>
        </div>

        <!-- Tombol Selanjutnya -->
        <button
            type="button"
            @click="goTo(currentPage + 1)"
            :disabled="currentPage >= totalPages"
            :class="navButtonClasses"
        >
            <span>{{ nextLabel }}</span>
            <span aria-hidden="true" class="sm:hidden">›</span>
            <span aria-hidden="true" class="hidden sm:inline">→</span>
        </button>
    </nav>
</template>
