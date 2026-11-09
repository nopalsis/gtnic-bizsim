<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    insights: {
        type: Array,
        required: true,
    },
    executedActions: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['action-executed', 'toggle-action', 'open-modal']);

const activeTab = ref('all'); // 'all' | 'sales' | 'waste' | 'stock'
const copiedActionId = ref(null);

const categoryCounts = computed(() => {
    return {
        all: props.insights.length,
        sales: props.insights.filter((i) => i.categoryGroup === 'sales').length,
        waste: props.insights.filter((i) => i.categoryGroup === 'waste').length,
        stock: props.insights.filter((i) => i.categoryGroup === 'stock').length,
    };
});

const filteredInsights = computed(() => {
    if (activeTab.value === 'all') return props.insights;
    return props.insights.filter((i) => i.categoryGroup === activeTab.value);
});

// Di tata letak bawah tabel (full width), tampilkan hingga 6 kartu (2 baris x 3 kolom)
const displayedInsights = computed(() => {
    return filteredInsights.value.slice(0, 6);
});

const copyInstruction = async (insight) => {
    try {
        await navigator.clipboard.writeText(insight.instruction);
        copiedActionId.value = insight.id;
        setTimeout(() => {
            copiedActionId.value = null;
        }, 2200);
        emit('action-executed', {
            id: insight.id,
            title: insight.title,
            status: 'copied',
            message: `Instruksi "${insight.title}" berhasil disalin ke clipboard!`,
        });
    } catch {
        copiedActionId.value = insight.id;
        setTimeout(() => {
            copiedActionId.value = null;
        }, 2200);
    }
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header: Judul & Tombol Buka Modal Lengkap -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-1">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#8fae26]"></span>
                <h3 class="text-xs font-black uppercase tracking-wider text-[#4d5240]">
                    Rekomendasi Tindakan Perbaikan
                </h3>
            </div>

            <!-- Tombol Buka Modal Tindakan Lengkap -->
            <button
                type="button"
                @click="emit('open-modal')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#0e0f0c] text-[#c6ef4e] hover:bg-[#2d4000] text-xs font-black transition-all cursor-pointer shadow-xs self-start sm:self-auto"
                title="Buka seluruh rekomendasi tindakan dalam modal khusus"
            >
                <span>⚡ Semua Tindakan</span>
                <span class="px-1.5 py-0.2 rounded-full bg-[#c6ef4e] text-[#0e0f0c] text-[10px]">
                    {{ insights.length }}
                </span>
            </button>
        </div>

        <!-- Filter Tabs: Semua / Penjualan / Limbah / Stok -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-bold">
            <button
                type="button"
                @click="activeTab = 'all'"
                class="px-3 py-1.5 rounded-xl transition-all cursor-pointer shrink-0"
                :class="activeTab === 'all' ? 'bg-[#0e0f0c] text-white shadow-xs' : 'bg-white border border-[#dce0cd] text-[#737868] hover:text-[#0e0f0c]'"
            >
                Semua ({{ categoryCounts.all }})
            </button>
            <button
                type="button"
                @click="activeTab = 'sales'"
                class="px-2.5 py-1.5 rounded-xl transition-all cursor-pointer shrink-0 flex items-center gap-1"
                :class="activeTab === 'sales' ? 'bg-[#0e0f0c] text-[#c6ef4e] shadow-xs' : 'bg-white border border-[#dce0cd] text-[#737868] hover:text-[#0e0f0c]'"
            >
                <span>🏷️</span> Penjualan ({{ categoryCounts.sales }})
            </button>
            <button
                type="button"
                @click="activeTab = 'waste'"
                class="px-2.5 py-1.5 rounded-xl transition-all cursor-pointer shrink-0 flex items-center gap-1"
                :class="activeTab === 'waste' ? 'bg-[#0e0f0c] text-[#c6ef4e] shadow-xs' : 'bg-white border border-[#dce0cd] text-[#737868] hover:text-[#0e0f0c]'"
            >
                <span>✂️</span> Limbah ({{ categoryCounts.waste }})
            </button>
            <button
                type="button"
                @click="activeTab = 'stock'"
                class="px-2.5 py-1.5 rounded-xl transition-all cursor-pointer shrink-0 flex items-center gap-1"
                :class="activeTab === 'stock' ? 'bg-[#0e0f0c] text-[#c6ef4e] shadow-xs' : 'bg-white border border-[#dce0cd] text-[#737868] hover:text-[#0e0f0c]'"
            >
                <span>📦</span> Stok ({{ categoryCounts.stock }})
            </button>
        </div>

        <!-- Action Cards Grid (Responsive Grid 1, 2, atau 3 Kolom) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div
                v-for="insight in displayedInsights"
                :key="insight.id"
                class="bg-white rounded-[26px] border transition-all p-5 lg:p-6 shadow-xs relative overflow-hidden flex flex-col justify-between"
                :class="[
                    executedActions[insight.id]
                        ? 'border-[#8fae26] bg-[#fafbf7] ring-1 ring-[#c6ef4e]'
                        : 'border-[#dce0cd] hover:border-[#8fae26]'
                ]"
            >
                <!-- Top Badge & Tag -->
                <div>
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-7 h-7 rounded-xl flex items-center justify-center text-xs font-black"
                                :class="[
                                    executedActions[insight.id]
                                        ? 'bg-[#0e0f0c] text-[#c6ef4e]'
                                        : 'bg-[#0e0f0c] text-white'
                                ]"
                            >
                                {{ insight.code }}
                            </span>
                            <div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full bg-[#f4f6ee] text-[#4d5240] border border-[#eaecdf]">
                                    {{ insight.category }}
                                </span>
                                <h4 class="text-sm font-black text-[#0e0f0c] mt-1 leading-tight">
                                    {{ insight.title }}
                                </h4>
                            </div>
                        </div>

                        <!-- Impact Badge -->
                        <span
                            class="text-[11px] font-black px-2.5 py-1 rounded-full shrink-0 border"
                            :class="[
                                executedActions[insight.id]
                                    ? 'bg-[#c6ef4e]/20 text-[#2d4000] border-[#c6ef4e]'
                                    : 'bg-[#fafbf7] text-[#4d5240] border-[#dce0cd]'
                            ]"
                        >
                            {{ executedActions[insight.id] ? '✓ ' + insight.activeStatus : insight.badge }}
                        </span>
                    </div>

                    <!-- 1. Masalah -->
                    <div class="mt-3 p-3 rounded-xl bg-rose-50/70 border border-rose-100 text-xs">
                        <div class="flex items-center gap-1.5 font-bold text-rose-900">
                            <span>🔴</span>
                            <span>Temuan Masalah:</span>
                            <span class="font-extrabold text-neutral-800">({{ insight.targetProduct }})</span>
                        </div>
                        <p class="text-rose-800 text-[11px] mt-1 leading-relaxed">
                            {{ insight.problem }}
                        </p>
                    </div>

                    <!-- 2. Instruksi Kerja -->
                    <div class="mt-3 space-y-1.5 text-xs">
                        <div class="p-3.5 rounded-2xl bg-[#0e0f0c] text-white border border-[#2d4000] relative">
                            <div class="text-[10px] font-black uppercase tracking-wider text-[#c6ef4e] mb-1 flex items-center gap-1">
                                <span>📋</span> Instruksi Kerja:
                            </div>
                            <p class="text-xs leading-relaxed font-semibold text-neutral-100">
                                "{{ insight.instruction }}"
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 3. Tombol Eksekusi & Salin Memo -->
                <div class="mt-4 pt-3.5 border-t border-[#eaecdf] flex flex-wrap items-center justify-between gap-2.5">
                    <button
                        type="button"
                        @click="copyInstruction(insight)"
                        class="px-3 py-2 rounded-xl border border-[#dce0cd] bg-[#fafbf7] hover:bg-white text-xs font-bold text-[#4d5240] flex items-center gap-1.5 cursor-pointer transition-all shadow-2xs"
                        :title="'Salin instruksi kerja ' + insight.title"
                    >
                        <span v-if="copiedActionId === insight.id" class="text-[#2d4000] font-black flex items-center gap-1">
                            ✓ Berhasil Disalin!
                        </span>
                        <span v-else class="flex items-center gap-1">
                            📋 Salin Instruksi
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="emit('toggle-action', insight)"
                        class="px-4 py-2 rounded-xl text-xs font-black cursor-pointer transition-all flex items-center gap-1.5 shadow-sm"
                        :class="[
                            executedActions[insight.id]
                                ? 'bg-[#c6ef4e] text-[#0e0f0c] hover:bg-[#b2e132]'
                                : 'bg-[#0e0f0c] text-white hover:bg-[#2d4000]'
                        ]"
                    >
                        <span v-if="executedActions[insight.id]">✓ {{ insight.activeStatus }}</span>
                        <span v-else>⚡ {{ insight.buttonText }}</span>
                    </button>
                </div>
            </div>

            <!-- More Actions Banner (Bila ada lebih dari 6 tindakan) -->
            <div
                v-if="filteredInsights.length > 6"
                class="col-span-full bg-white rounded-2xl border border-[#dce0cd] p-4 text-center space-y-2 shadow-2xs"
            >
                <p class="text-xs text-[#737868]">
                    Menampilkan <strong>6 rekomendasi utama</strong> dari total <strong>{{ filteredInsights.length }} rekomendasi</strong> di kategori ini.
                </p>
                <button
                    type="button"
                    @click="emit('open-modal')"
                    class="px-4 py-2 rounded-xl bg-[#fafbf7] hover:bg-[#f4f6ee] text-[#0e0f0c] border border-[#dce0cd] text-xs font-extrabold cursor-pointer transition-colors inline-flex items-center gap-2"
                >
                    <span>🔍 Buka Modal Pusat Tindakan Lengkap</span>
                    <span class="px-2 py-0.5 rounded-full bg-[#0e0f0c] text-[#c6ef4e] text-[10px]">
                        +{{ filteredInsights.length - 6 }} Lainnya
                    </span>
                </button>
            </div>

            <div v-if="displayedInsights.length === 0" class="col-span-full bg-white rounded-2xl border border-[#dce0cd] p-8 text-center text-neutral-400 text-xs">
                Tidak ada rekomendasi tindakan untuk kategori ini.
            </div>
        </div>
    </div>
</template>
