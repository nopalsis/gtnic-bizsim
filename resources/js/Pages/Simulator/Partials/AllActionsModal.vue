<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    insights: {
        type: Array,
        required: true,
    },
    executedActions: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['close', 'toggle-action', 'copy-instruction', 'copy-all']);

const activeCategoryFilter = ref('all');
const activeStatusFilter = ref('all');
const searchQuery = ref('');
const copiedActionId = ref(null);
const copiedAllSuccess = ref(false);

const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};

const categoryCounts = computed(() => {
    return {
        all: props.insights.length,
        sales: props.insights.filter((i) => i.categoryGroup === 'sales').length,
        waste: props.insights.filter((i) => i.categoryGroup === 'waste').length,
        stock: props.insights.filter((i) => i.categoryGroup === 'stock').length,
    };
});

const totalPotentialSavings = computed(() => {
    return props.insights.reduce((acc, curr) => acc + (curr.impactValue || 0), 0);
});

const activeExecutedCount = computed(() => {
    return Object.values(props.executedActions).filter(Boolean).length;
});

const filteredInsights = computed(() => {
    return props.insights.filter((ins) => {
        const matchCategory =
            activeCategoryFilter.value === 'all' || ins.categoryGroup === activeCategoryFilter.value;

        const isExecuted = !!props.executedActions[ins.id];
        const matchStatus =
            activeStatusFilter.value === 'all' ||
            (activeStatusFilter.value === 'active' && isExecuted) ||
            (activeStatusFilter.value === 'pending' && !isExecuted);

        const q = searchQuery.value.toLowerCase().trim();
        const matchSearch =
            !q ||
            ins.title.toLowerCase().includes(q) ||
            ins.targetProduct.toLowerCase().includes(q) ||
            ins.instruction.toLowerCase().includes(q) ||
            ins.problem.toLowerCase().includes(q);

        return matchCategory && matchStatus && matchSearch;
    });
});

const handleCopySingle = async (insight) => {
    try {
        await navigator.clipboard.writeText(insight.instruction);
        copiedActionId.value = insight.id;
        setTimeout(() => {
            copiedActionId.value = null;
        }, 2200);
        emit('copy-instruction', insight);
    } catch {
        copiedActionId.value = insight.id;
        setTimeout(() => {
            copiedActionId.value = null;
        }, 2200);
    }
};

const handleCopyAllBriefing = async () => {
    const text = props.insights
        .map((ins, idx) => {
            return `[${ins.code}] ${ins.title}\nTarget: ${ins.targetProduct}\nMasalah: ${ins.problem}\nInstruksi: "${ins.instruction}"\nDampak: ${ins.impact}\n`;
        })
        .join('\n----------------------------------------\n\n');

    const header = `=== REKAP SOP & TINDAKAN AUDIT WORKSHOP OPTIMABIZ ===\nTotal Rekomendasi: ${props.insights.length} Tindakan\nTanggal Audit: ${new Date().toLocaleDateString('id-ID')}\n\n`;

    try {
        await navigator.clipboard.writeText(header + text);
        copiedAllSuccess.value = true;
        setTimeout(() => {
            copiedAllSuccess.value = false;
        }, 2500);
        emit('copy-all');
    } catch {
        copiedAllSuccess.value = true;
        setTimeout(() => {
            copiedAllSuccess.value = false;
        }, 2500);
    }
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-[#0e0f0c]/60 backdrop-blur-sm"
        @click.self="emit('close')"
    >
        <div class="bg-white rounded-[28px] w-full max-w-4xl shadow-2xl border border-[#dce0cd] overflow-hidden flex flex-col max-h-[92vh]">
            <!-- Header Modal -->
            <div class="p-5 sm:p-6 border-b border-[#eaecdf] bg-[#fafbf7] flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-[#0e0f0c] text-[#c6ef4e] text-xs font-black uppercase tracking-wider">
                            <span>⚡</span> {{ insights.length }} Tindakan Siap Eksekusi
                        </span>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black text-[#0e0f0c] tracking-tight">
                        Rekomendasi Tindakan Perbaikan
                    </h3>
                    <p class="text-xs sm:text-sm text-[#737868] mt-0.5 font-medium">
                        Instruksi kerja konkret untuk mengatasi kebocoran kas, menekan limbah kulit, dan mencairkan stok mandek.
                    </p>
                </div>

                <button
                    type="button"
                    @click="emit('close')"
                    class="w-9 h-9 rounded-full bg-neutral-200 hover:bg-neutral-300 flex items-center justify-center text-neutral-700 cursor-pointer font-bold transition-colors shrink-0"
                >
                    ✕
                </button>
            </div>

            <!-- Stats Bar (Potensi Kas Diselamatkan) -->
            <div class="bg-[#0e0f0c] text-white px-5 sm:px-6 py-3 border-y border-[#2d4000] flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-4 flex-wrap">
                    <span class="text-neutral-400">
                        Total Rekomendasi: <strong class="text-white">{{ insights.length }} Tindakan</strong>
                    </span>
                    <span class="text-neutral-500">|</span>
                    <span class="text-neutral-400">
                        Status Dijalankan: <strong class="text-[#c6ef4e]">{{ activeExecutedCount }} Aktif</strong>
                    </span>
                    <span class="text-neutral-500">|</span>
                    <span class="text-neutral-400">
                        Estimasi Kas Diselamatkan: <strong class="text-white">{{ formatRupiah(totalPotentialSavings) }}</strong>
                    </span>
                </div>

                <button
                    type="button"
                    @click="handleCopyAllBriefing"
                    class="px-3 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-[11px] font-bold text-[#c6ef4e] transition-colors cursor-pointer flex items-center gap-1.5"
                >
                    <span v-if="copiedAllSuccess">✓ Rekap Semua Tindakan Berhasil Disalin!</span>
                    <span v-else>📋 Salin Seluruh SOP Tindakan</span>
                </button>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="p-4 sm:p-5 bg-white border-b border-[#eaecdf] flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <!-- Category Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 text-xs font-bold shrink-0">
                    <button
                        type="button"
                        @click="activeCategoryFilter = 'all'"
                        class="px-3 py-1.5 rounded-xl transition-all cursor-pointer shrink-0"
                        :class="activeCategoryFilter === 'all' ? 'bg-[#0e0f0c] text-white shadow-xs' : 'bg-[#fafbf7] border border-[#dce0cd] text-[#737868] hover:text-[#0e0f0c]'"
                    >
                        Semua ({{ categoryCounts.all }})
                    </button>
                    <button
                        type="button"
                        @click="activeCategoryFilter = 'sales'"
                        class="px-3 py-1.5 rounded-xl transition-all cursor-pointer shrink-0 flex items-center gap-1"
                        :class="activeCategoryFilter === 'sales' ? 'bg-[#0e0f0c] text-[#c6ef4e] shadow-xs' : 'bg-[#fafbf7] border border-[#dce0cd] text-[#737868] hover:text-[#0e0f0c]'"
                    >
                        <span>🏷️</span> Penjualan & Harga ({{ categoryCounts.sales }})
                    </button>
                    <button
                        type="button"
                        @click="activeCategoryFilter = 'waste'"
                        class="px-3 py-1.5 rounded-xl transition-all cursor-pointer shrink-0 flex items-center gap-1"
                        :class="activeCategoryFilter === 'waste' ? 'bg-[#0e0f0c] text-[#c6ef4e] shadow-xs' : 'bg-[#fafbf7] border border-[#dce0cd] text-[#737868] hover:text-[#0e0f0c]'"
                    >
                        <span>✂️</span> Meja Potong & Limbah ({{ categoryCounts.waste }})
                    </button>
                    <button
                        type="button"
                        @click="activeCategoryFilter = 'stock'"
                        class="px-3 py-1.5 rounded-xl transition-all cursor-pointer shrink-0 flex items-center gap-1"
                        :class="activeCategoryFilter === 'stock' ? 'bg-[#0e0f0c] text-[#c6ef4e] shadow-xs' : 'bg-[#fafbf7] border border-[#dce0cd] text-[#737868] hover:text-[#0e0f0c]'"
                    >
                        <span>📦</span> Stok & Kas Gudang ({{ categoryCounts.stock }})
                    </button>
                </div>

                <!-- Search Input & Status Toggle -->
                <div class="flex items-center gap-2">
                    <select
                        v-model="activeStatusFilter"
                        class="bg-[#fafbf7] text-[#0e0f0c] border border-[#dce0cd] rounded-xl px-2.5 py-1.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-[#8fae26]"
                    >
                        <option value="all">Semua Status</option>
                        <option value="pending">Belum Dijalankan</option>
                        <option value="active">Sudah Dijalankan</option>
                    </select>

                    <div class="relative w-full sm:w-56">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari instruksi..."
                            class="w-full bg-[#fafbf7] border border-[#dce0cd] rounded-xl px-3 py-1.5 pl-8 text-xs text-[#0e0f0c] placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-[#8fae26]"
                        />
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-neutral-400 text-xs">
                            🔍
                        </span>
                        <button
                            v-if="searchQuery"
                            type="button"
                            @click="searchQuery = ''"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 text-xs cursor-pointer"
                        >
                            ✕
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content List (Scrollable Cards) -->
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 bg-[#f8f9f4]">
                <div
                    v-for="insight in filteredInsights"
                    :key="insight.id"
                    class="bg-white rounded-2xl border transition-all p-5 shadow-xs"
                    :class="[
                        executedActions[insight.id]
                            ? 'border-[#8fae26] ring-1 ring-[#c6ef4e]'
                            : 'border-[#dce0cd]'
                    ]"
                >
                    <!-- Card Top Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-[#eaecdf]">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="w-7 h-7 rounded-xl flex items-center justify-center text-xs font-black shrink-0"
                                :class="[
                                    executedActions[insight.id]
                                        ? 'bg-[#0e0f0c] text-[#c6ef4e]'
                                        : 'bg-[#0e0f0c] text-white'
                                ]"
                            >
                                {{ insight.code }}
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-[#f4f6ee] text-[#4d5240] border border-[#eaecdf]">
                                        {{ insight.category }}
                                    </span>
                                    <span class="text-xs text-neutral-400">·</span>
                                    <span class="text-xs font-bold text-[#0e0f0c]">
                                        Target: {{ insight.targetProduct }}
                                    </span>
                                </div>
                                <h4 class="text-sm sm:text-base font-black text-[#0e0f0c] mt-0.5">
                                    {{ insight.title }}
                                </h4>
                            </div>
                        </div>

                        <!-- Status & Impact Badge -->
                        <div class="flex items-center gap-2 self-start sm:self-auto">
                            <span
                                class="text-xs font-black px-2.5 py-1 rounded-full border"
                                :class="[
                                    executedActions[insight.id]
                                        ? 'bg-[#c6ef4e]/20 text-[#2d4000] border-[#c6ef4e]'
                                        : 'bg-[#fafbf7] text-[#4d5240] border-[#dce0cd]'
                                ]"
                            >
                                {{ executedActions[insight.id] ? '✓ ' + insight.activeStatus : insight.badge }}
                            </span>
                        </div>
                    </div>

                    <!-- Problem & Action Grid -->
                    <div class="mt-3.5 grid grid-cols-1 md:grid-cols-12 gap-3.5">
                        <!-- Left: Problem & Impact -->
                        <div class="md:col-span-5 space-y-2">
                            <div class="p-3 rounded-xl bg-rose-50/70 border border-rose-100 text-xs">
                                <div class="font-bold text-rose-900 flex items-center gap-1.5 mb-1">
                                    <span>🔴</span> Masalah Lapangan:
                                </div>
                                <p class="text-rose-800 text-[11px] leading-relaxed">
                                    {{ insight.problem }}
                                </p>
                            </div>

                            <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-100 text-xs">
                                <div class="font-bold text-emerald-900 flex items-center gap-1.5 mb-1">
                                    <span>💰</span> Potensi Penyelamatan Kas:
                                </div>
                                <p class="text-emerald-800 text-[11px] font-semibold leading-relaxed">
                                    {{ insight.impact }}
                                </p>
                            </div>
                        </div>

                        <!-- Right: System Action & Exact Instruction -->
                        <div class="md:col-span-7 flex flex-col justify-between space-y-2">
                            <div class="p-3.5 rounded-2xl bg-[#0e0f0c] text-white border border-[#2d4000] relative">
                                <div class="text-[10px] font-black uppercase tracking-wider text-[#c6ef4e] mb-1 flex items-center gap-1.5">
                                    <span>📋</span> Instruksi Sistem untuk Dijalankan:
                                </div>
                                <p class="text-xs leading-relaxed font-semibold text-neutral-100">
                                    "{{ insight.instruction }}"
                                </p>
                                <div class="text-[11px] text-neutral-400 mt-2 pt-2 border-t border-white/10 flex items-center gap-1">
                                    <span class="text-[#c6ef4e]">⚙️ Teknis:</span> {{ insight.systemAction }}
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="flex items-center justify-end gap-2 pt-1">
                                <button
                                    type="button"
                                    @click="handleCopySingle(insight)"
                                    class="px-3 py-1.5 rounded-xl border border-[#dce0cd] bg-white hover:bg-neutral-50 text-xs font-bold text-[#4d5240] flex items-center gap-1.5 cursor-pointer transition-colors shadow-2xs"
                                >
                                    <span v-if="copiedActionId === insight.id" class="text-[#2d4000] font-black">
                                        ✓ Berhasil Disalin!
                                    </span>
                                    <span v-else>
                                        📋 Salin Teks Instruksi
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    @click="emit('toggle-action', insight)"
                                    class="px-4 py-1.5 rounded-xl text-xs font-black cursor-pointer transition-all flex items-center gap-1.5 shadow-sm"
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

                <div v-if="filteredInsights.length === 0" class="py-12 text-center text-neutral-400">
                    <p class="text-sm font-bold">Tidak ada rekomendasi tindakan yang sesuai filter.</p>
                    <p class="text-xs mt-1">Coba pilih filter kategori atau hapus kata kunci pencarian.</p>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="p-4 bg-[#fafbf7] border-t border-[#eaecdf] flex items-center justify-between">
                <span class="text-xs text-[#737868] hidden sm:inline">
                    💡 Rekomendasi dihitung otomatis berdasarkan audit 30 hari terakhir.
                </span>
                <button
                    type="button"
                    @click="emit('close')"
                    class="px-6 py-2 rounded-xl bg-[#0e0f0c] text-white text-xs font-black hover:bg-[#2d4000] cursor-pointer transition-colors ml-auto shadow-sm"
                >
                    Tutup Modal
                </button>
            </div>
        </div>
    </div>
</template>

