<script setup>
import { ref, watch, nextTick, onMounted } from 'vue';
import { useChartSelection } from '@/Composables/useChartSelection';

const props = defineProps({
    bars: {
        type: Array,
        default: () => [],
    },
    hintText: {
        type: String,
        default: 'Ketuk batang grafik untuk melihat detail',
    },
    emptyText: {
        type: String,
        default: 'Belum ada data grafik.',
    },
});

const scrollContainer = ref(null);

const {
    selectedItem,
    activeItem,
    isActive,
    onPointerEnter,
    onPointerLeave,
    toggleSelect,
    clearSelection,
} = useChartSelection((bar) => `${bar.date}-${bar.amount ?? ''}-${bar.count ?? ''}`);

const scrollToLatest = async () => {
    await nextTick();
    if (scrollContainer.value && props.bars.length > 7) {
        scrollContainer.value.scrollLeft = scrollContainer.value.scrollWidth;
    }
};

onMounted(scrollToLatest);
watch(
    () => props.bars,
    () => {
        clearSelection();
        scrollToLatest();
    },
);
</script>

<template>
    <div>
        <!-- Strip Rincian Interaktif (Aman dari terpotong di layar mobile & desktop) -->
        <div
            v-if="activeItem"
            class="mb-3 px-3 py-2 rounded-xl bg-[#0e0f0c] text-white flex items-center justify-between gap-2 text-xs shadow-md border border-neutral-800 transition-all"
        >
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 min-w-0">
                <slot name="detail" :bar="activeItem" />
            </div>

            <button
                v-if="selectedItem"
                type="button"
                @click="clearSelection"
                class="w-5 h-5 rounded-full bg-neutral-800 hover:bg-neutral-700 text-neutral-300 hover:text-white text-[10px] font-bold flex items-center justify-center shrink-0 cursor-pointer"
                title="Tutup rincian"
                aria-label="Tutup rincian"
            >
                ✕
            </button>
        </div>

        <!-- Strip Petunjuk Standar saat tidak ada batang aktif -->
        <div
            v-else
            class="mb-3 px-3 py-2 rounded-xl bg-[#fafbf7] border border-[#e5e9db] text-[#737868] flex items-center gap-2 text-[11px]"
        >
            <span class="material-symbols-outlined text-[15px] text-[#0e0f0c] shrink-0">touch_app</span>
            <span class="truncate">{{ hintText }}</span>
        </div>

        <!-- Area Grafik Batang (Scrollable horizontal jika > 7 hari agar batang tetap nyaman diketuk) -->
        <div class="relative w-full h-48 bg-[#fdfdfc] rounded-2xl border border-[#ecefe4] p-2.5 sm:p-3 flex flex-col justify-end">
            <!-- Garis Bantu Horizontal -->
            <div class="absolute inset-x-3 top-3 bottom-8 flex flex-col justify-between pointer-events-none opacity-40">
                <div class="border-b border-dashed border-[#dce0cd] w-full"></div>
                <div class="border-b border-dashed border-[#dce0cd] w-full"></div>
                <div class="border-b border-[#dce0cd] w-full"></div>
            </div>

            <div
                v-if="bars.length > 0"
                ref="scrollContainer"
                class="relative z-10 w-full h-full overflow-x-auto"
            >
                <div class="flex items-end justify-around gap-1.5 sm:gap-2 h-full min-w-full w-max">
                    <div
                        v-for="(bar, idx) in bars"
                        :key="'sbar-' + idx"
                        role="button"
                        tabindex="0"
                        :aria-pressed="isActive(bar)"
                        :aria-label="`Detail ${bar.date}`"
                        @click="toggleSelect(bar)"
                        @keydown.enter.prevent="toggleSelect(bar)"
                        @keydown.space.prevent="toggleSelect(bar)"
                        @pointerenter="onPointerEnter($event, bar)"
                        @pointerleave="onPointerLeave($event)"
                        :class="[
                            'flex-1 min-w-[42px] max-w-[80px] h-full flex flex-col items-center justify-end group cursor-pointer rounded-xl py-1 px-0.5 transition-colors select-none touch-manipulation outline-none',
                            isActive(bar)
                                ? 'bg-neutral-100/90 ring-1 ring-[#a2c833]'
                                : 'hover:bg-neutral-100/70 focus-visible:ring-1 focus-visible:ring-[#0e0f0c]'
                        ]"
                    >
                        <div class="w-full h-32 flex items-end justify-center pb-1">
                            <div
                                :class="[
                                    'w-full max-w-[32px] rounded-t-lg transition-all duration-300',
                                    isActive(bar)
                                        ? 'bg-[#c6ef4e] border border-[#a2c833] shadow-xs'
                                        : 'bg-[#0e0f0c] group-hover:bg-[#c6ef4e]'
                                ]"
                                :style="{ height: `${bar.heightPct}%` }"
                            ></div>
                        </div>

                        <span
                            :class="[
                                'text-[9px] sm:text-[10px] mt-1 truncate w-full text-center transition-colors',
                                isActive(bar)
                                    ? 'font-black text-[#0e0f0c]'
                                    : 'font-bold text-[#737868] group-hover:text-[#0e0f0c]'
                            ]"
                        >
                            {{ bar.date }}
                        </span>
                    </div>
                </div>
            </div>

            <div v-else class="h-full flex items-center justify-center text-xs text-[#737868] font-medium">
                {{ emptyText }}
            </div>
        </div>
    </div>
</template>
