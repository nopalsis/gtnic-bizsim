<script setup>
import { ref } from 'vue';

const props = defineProps({
    insights: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['action-executed']);

const executedActions = ref({
    floor_price_guardrail: false,
    smart_craftsman_assignment: false,
    cash_liquidation_clearance: false,
});

const copiedActionId = ref(null);

const executeAction = (insight) => {
    executedActions.value[insight.id] = !executedActions.value[insight.id];
    emit('action-executed', {
        id: insight.id,
        title: insight.title,
        status: executedActions.value[insight.id] ? 'active' : 'inactive',
        message: executedActions.value[insight.id]
            ? `${insight.activeStatus} berhasil diaktifkan!`
            : `Status ${insight.title} dinonaktifkan.`,
    });
};

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
        // Fallback if clipboard API is restricted
        copiedActionId.value = insight.id;
        setTimeout(() => {
            copiedActionId.value = null;
        }, 2200);
    }
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header Panel Kanan -->
        <div class="flex items-center justify-between px-1">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#8fae26] animate-pulse"></span>
                <h3 class="text-xs font-black uppercase tracking-wider text-[#4d5240]">
                    Kotak Tindakan Konkret (Actionable Insights)
                </h3>
            </div>
            <span class="text-xs text-[#737868]">3 Instruksi Eksekusi Hari Ini</span>
        </div>

        <!-- Action Cards Container -->
        <div class="space-y-4">
            <div
                v-for="insight in insights"
                :key="insight.id"
                class="bg-white rounded-[26px] border transition-all p-5 lg:p-6 shadow-xs relative overflow-hidden"
                :class="[
                    executedActions[insight.id]
                        ? 'border-[#8fae26] bg-[#fafbf7] ring-1 ring-[#c6ef4e]'
                        : 'border-[#dce0cd] hover:border-[#8fae26]'
                ]"
            >
                <!-- Top Badge & Tag -->
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

                <!-- 1. Masalah Lapangan -->
                <div class="mt-3 p-3 rounded-xl bg-rose-50/60 border border-rose-100 text-xs">
                    <div class="flex items-center gap-1.5 font-bold text-rose-900">
                        <span>🔴</span>
                        <span>Masalah Lapangan:</span>
                        <span class="font-extrabold text-neutral-800">({{ insight.targetProduct }})</span>
                    </div>
                    <p class="text-rose-800 text-[11px] mt-1 leading-relaxed">
                        {{ insight.problem }}
                    </p>
                </div>

                <!-- 2. Tindakan Sistem & Instruksi Kerja -->
                <div class="mt-3 space-y-1.5 text-xs">
                    <div class="text-[11px] text-[#737868] flex items-center gap-1.5 font-medium">
                        <span class="text-neutral-700 font-bold">⚙️ Tindakan Sistem:</span>
                        <span class="text-[#0e0f0c] font-bold">{{ insight.systemAction }}</span>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-[#0e0f0c] text-white border border-[#2d4000] relative">
                        <div class="text-[10px] font-black uppercase tracking-wider text-[#c6ef4e] mb-1 flex items-center gap-1">
                            <span>📋</span> Instruksi Sistem untuk Dijalankan:
                        </div>
                        <p class="text-xs leading-relaxed font-semibold text-neutral-100">
                            "{{ insight.instruction }}"
                        </p>
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
                        @click="executeAction(insight)"
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
        </div>
    </div>
</template>

