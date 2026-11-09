<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        required: true,
    },
    selectedProductId: {
        type: [Number, String, null],
        default: null,
    },
});

const emit = defineEmits(['select-product']);

const activeStatusFilter = ref('all');
const searchQuery = ref('');

const filteredProducts = computed(() => {
    return props.products.filter((prod) => {
        const matchStatus =
            activeStatusFilter.value === 'all' || prod.status === activeStatusFilter.value;
        const q = searchQuery.value.toLowerCase().trim();
        const matchSearch =
            !q ||
            prod.name.toLowerCase().includes(q) ||
            prod.category.toLowerCase().includes(q) ||
            prod.leakPoint.toLowerCase().includes(q) ||
            prod.sku.toLowerCase().includes(q);

        return matchStatus && matchSearch;
    });
});

const statusCount = computed(() => {
    return {
        all: props.products.length,
        kritis: props.products.filter((p) => p.status === 'kritis').length,
        waspada: props.products.filter((p) => p.status === 'waspada').length,
        sehat: props.products.filter((p) => p.status === 'sehat').length,
    };
});
</script>

<template>
    <div class="bg-white rounded-[26px] border border-[#dce0cd] shadow-xs overflow-hidden flex flex-col justify-between">
        <!-- Header Panel Kiri -->
        <div class="p-5 lg:p-6 border-b border-[#eaecdf]">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#f4f6ee] text-[#4d5240] text-xs font-bold mb-1">
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                        Panel Audit Per Model
                    </div>
                    <h3 class="text-xl font-black text-[#0e0f0c] tracking-tight">
                        Mana Model yang Bocor?
                    </h3>
                    <p class="text-xs text-[#737868] mt-0.5">
                        Membedah selisih target margin vs realisasi di lapangan. Klik baris produk untuk rincian HPP riil.
                    </p>
                </div>

                <!-- Status Filter Tabs -->
                <div class="flex items-center gap-1.5 bg-[#fafbf7] p-1 rounded-2xl border border-[#dce0cd] text-xs font-bold shrink-0">
                    <button
                        type="button"
                        @click="activeStatusFilter = 'all'"
                        class="px-2.5 py-1 rounded-xl transition-all cursor-pointer"
                        :class="activeStatusFilter === 'all' ? 'bg-[#0e0f0c] text-white shadow-xs' : 'text-[#737868] hover:text-[#0e0f0c]'"
                    >
                        Semua ({{ statusCount.all }})
                    </button>
                    <button
                        type="button"
                        @click="activeStatusFilter = 'kritis'"
                        class="px-2.5 py-1 rounded-xl transition-all cursor-pointer flex items-center gap-1"
                        :class="activeStatusFilter === 'kritis' ? 'bg-rose-600 text-white shadow-xs' : 'text-rose-700 hover:bg-rose-50'"
                    >
                        <span>🔴</span> Kritis ({{ statusCount.kritis }})
                    </button>
                    <button
                        type="button"
                        @click="activeStatusFilter = 'waspada'"
                        class="px-2.5 py-1 rounded-xl transition-all cursor-pointer flex items-center gap-1"
                        :class="activeStatusFilter === 'waspada' ? 'bg-amber-500 text-white shadow-xs' : 'text-amber-700 hover:bg-amber-50'"
                    >
                        <span>🟡</span> Waspada ({{ statusCount.waspada }})
                    </button>
                    <button
                        type="button"
                        @click="activeStatusFilter = 'sehat'"
                        class="px-2.5 py-1 rounded-xl transition-all cursor-pointer flex items-center gap-1"
                        :class="activeStatusFilter === 'sehat' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-700 hover:bg-emerald-50'"
                    >
                        <span>🟢</span> Sehat ({{ statusCount.sehat }})
                    </button>
                </div>
            </div>

            <!-- Quick Search Bar -->
            <div class="mt-4 relative">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari model sepatu, sandal, atau kata kunci kebocoran..."
                    class="w-full bg-[#fafbf7] border border-[#dce0cd] rounded-xl px-3.5 py-2 pl-9 text-xs text-[#0e0f0c] placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-[#8fae26]"
                />
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-xs">
                    🔍
                </span>
                <button
                    v-if="searchQuery"
                    type="button"
                    @click="searchQuery = ''"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 text-xs cursor-pointer"
                >
                    ✕
                </button>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#fafbf7] text-[#737868] border-b border-[#eaecdf] font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Model Sepatu / Sandal</th>
                        <th class="py-3 px-3 text-right">Target Margin</th>
                        <th class="py-3 px-3 text-right">Realisasi Riil</th>
                        <th class="py-3 px-4">Titik Bocor Utama</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eaecdf]">
                    <tr
                        v-for="prod in filteredProducts"
                        :key="prod.id"
                        @click="emit('select-product', prod)"
                        class="cursor-pointer transition-all hover:bg-[#f4f6ee]/70 group"
                        :class="[
                            selectedProductId === prod.id ? 'bg-[#f4f6ee] ring-1 ring-[#8fae26] ring-inset' : ''
                        ]"
                    >
                        <!-- 1. Model Sepatu / Sandal -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-bold shrink-0"
                                    :class="[
                                        prod.status === 'kritis'
                                            ? 'bg-rose-100 text-rose-800'
                                            : prod.status === 'waspada'
                                                ? 'bg-amber-100 text-amber-800'
                                                : 'bg-emerald-100 text-emerald-800'
                                    ]"
                                >
                                    {{ prod.status === 'kritis' ? '🔴' : (prod.status === 'waspada' ? '🟡' : '🟢') }}
                                </div>
                                <div>
                                    <div class="font-extrabold text-[#0e0f0c] group-hover:text-[#2d4000] flex items-center gap-1.5">
                                        <span>{{ prod.name }}</span>
                                        <span class="text-[10px] text-neutral-400 group-hover:inline opacity-0 group-hover:opacity-100 transition-opacity">
                                            🔍 Bedah HPP
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-[#737868] flex items-center gap-1.5 mt-0.5">
                                        <span>{{ prod.category }}</span>
                                        <span>·</span>
                                        <span class="font-mono">{{ prod.sku }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- 2. Target Margin -->
                        <td class="py-3.5 px-3 text-right">
                            <div class="font-bold text-[#0e0f0c]">
                                {{ prod.targetMargin }}
                            </div>
                            <div class="text-[10px] text-[#737868]">Katalog Standar</div>
                        </td>

                        <!-- 3. Realisasi Riil -->
                        <td class="py-3.5 px-3 text-right">
                            <div
                                class="font-extrabold"
                                :class="[
                                    prod.status === 'kritis'
                                        ? 'text-rose-600'
                                        : prod.status === 'waspada'
                                            ? 'text-amber-600'
                                            : 'text-emerald-700'
                                ]"
                            >
                                {{ prod.realMargin }}
                            </div>
                            <div class="text-[10px]" :class="prod.status === 'kritis' ? 'text-rose-500 font-bold' : 'text-[#737868]'">
                                {{ prod.status === 'kritis' ? '⚠️ Anjlok -57%' : (prod.status === 'waspada' ? 'Tertekan' : 'Sesuai Target') }}
                            </div>
                        </td>

                        <!-- 4. Titik Bocor Utama -->
                        <td class="py-3.5 px-4">
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold"
                                :class="[
                                    prod.status === 'kritis'
                                        ? 'bg-rose-50 text-rose-800 border border-rose-200'
                                        : prod.status === 'waspada'
                                            ? 'bg-amber-50 text-amber-800 border border-amber-200'
                                            : 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                                ]"
                            >
                                {{ prod.leakPoint }}
                            </span>
                        </td>

                        <!-- 5. Status Badge -->
                        <td class="py-3.5 px-4 text-center">
                            <span
                                class="inline-block px-2.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wider"
                                :class="[
                                    prod.status === 'kritis'
                                        ? 'bg-rose-600 text-white'
                                        : prod.status === 'waspada'
                                            ? 'bg-amber-500 text-white'
                                            : 'bg-emerald-600 text-white'
                                ]"
                            >
                                {{ prod.status === 'kritis' ? '🔴 Kritis' : (prod.status === 'waspada' ? '🟡 Waspada' : '🟢 Sehat') }}
                            </span>
                        </td>
                    </tr>

                    <tr v-if="filteredProducts.length === 0">
                        <td colspan="5" class="py-8 text-center text-neutral-400">
                            Tidak ada model produk yang sesuai dengan filter audit.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer Hint -->
        <div class="p-3.5 bg-[#fafbf7] border-t border-[#eaecdf] flex items-center justify-between text-[11px] text-[#737868]">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#8fae26]"></span>
                <span>💡 <strong>Tips:</strong> Klik salah satu baris produk di atas untuk melihat rincian pembentuk HPP riil per pasang sepatu/sandal.</span>
            </div>
            <span class="font-bold text-[#0e0f0c]">{{ filteredProducts.length }} Model Diaudit</span>
        </div>
    </div>
</template>

