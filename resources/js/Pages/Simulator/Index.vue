<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import SidebarNav from '@/Components/SidebarNav.vue';

const props = defineProps({
    baseline: {
        type: Object,
        default: () => ({}),
    },
});

// UI State
const showSettingsModal = ref(false);
const isSaving = ref(false);

// Slider Simulation State
const priceAdjustmentPct = ref(0); // -30% to +30%
const volumeAdjustmentPct = ref(0); // -50% to +100%
const simEmployeeCount = ref(props.baseline?.employeeCount || 5);
const simSalaryPerEmployee = ref(props.baseline?.salaryPerEmployee || 3200000);
const simMonthlyFixedCost = ref(props.baseline?.monthlyFixedCost || 6500000);
const simWasteReductionPct = ref(0); // 0% to 80%

// Format Currency
const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};

// Reset to baseline
const resetSimulation = () => {
    priceAdjustmentPct.value = 0;
    volumeAdjustmentPct.value = 0;
    simEmployeeCount.value = props.baseline?.employeeCount || 5;
    simSalaryPerEmployee.value = props.baseline?.salaryPerEmployee || 3200000;
    simMonthlyFixedCost.value = props.baseline?.monthlyFixedCost || 6500000;
    simWasteReductionPct.value = 0;
};

// Simulation Math Projections
const projectedVolume = computed(() => {
    const baseVol = props.baseline?.totalItemsSold || 0;
    return Math.round(baseVol * (1 + volumeAdjustmentPct.value / 100));
});

const projectedPricePerUnit = computed(() => {
    const basePrice = props.baseline?.avgPricePerUnit || 450000;
    return Math.round(basePrice * (1 + priceAdjustmentPct.value / 100));
});

const projectedRevenue = computed(() => {
    return projectedVolume.value * projectedPricePerUnit.value;
});

const projectedCogs = computed(() => {
    const costPerUnit = props.baseline?.avgCostPerUnit || 220000;
    return projectedVolume.value * costPerUnit;
});

const projectedGrossProfit = computed(() => {
    return projectedRevenue.value - projectedCogs.value;
});

const projectedGrossMargin = computed(() => {
    return projectedRevenue.value > 0
        ? roundVal((projectedGrossProfit.value / projectedRevenue.value) * 100, 1)
        : 0;
});

const projectedFixedCost = computed(() => {
    const salary = simEmployeeCount.value * simSalaryPerEmployee.value;
    return salary + simMonthlyFixedCost.value;
});

const projectedWasteCost = computed(() => {
    const baseWaste = props.baseline?.wasteCost || 0;
    const factor = Math.max(0, 1 - (simWasteReductionPct.value / 100));
    return Math.round(baseWaste * factor);
});

const projectedNetProfit = computed(() => {
    return projectedGrossProfit.value - (projectedFixedCost.value + projectedWasteCost.value);
});

const projectedBep = computed(() => {
    return projectedGrossMargin.value > 0
        ? Math.round(projectedFixedCost.value / (projectedGrossMargin.value / 100))
        : 0;
});

const projectedCapacityUsage = computed(() => {
    const maxCap = props.baseline?.monthlyMaxCapacity || 500;
    return maxCap > 0 ? roundVal((projectedVolume.value / maxCap) * 100, 1) : 0;
});

const netProfitDifference = computed(() => {
    return projectedNetProfit.value - (props.baseline?.netProfit || 0);
});

const netProfitPctChange = computed(() => {
    const baseNet = props.baseline?.netProfit || 1;
    return baseNet !== 0 ? roundVal((netProfitDifference.value / Math.abs(baseNet)) * 100, 1) : 0;
});

function roundVal(val, dec = 1) {
    const factor = Math.pow(10, dec);
    return Math.round(val * factor) / factor;
}

// Settings Update Form State
const settingsForm = ref({
    workshop_name: props.baseline?.workshopName || 'OptimaBiz Leathercraft & Shoes',
    employee_count: props.baseline?.employeeCount || 5,
    salary_per_employee: props.baseline?.salaryPerEmployee || 3200000,
    monthly_fixed_cost: props.baseline?.monthlyFixedCost || 6500000,
    monthly_max_capacity: props.baseline?.monthlyMaxCapacity || 500,
});

const submitSettings = () => {
    isSaving.value = true;
    router.post('/simulator/settings', settingsForm.value, {
        onSuccess: () => {
            showSettingsModal.value = false;
            isSaving.value = false;
        },
        onError: () => {
            isSaving.value = false;
        },
    });
};
</script>

<template>
    <Head title="What-If Scenario Simulator · OptimaBiz" />

    <div class="min-h-screen w-full bg-[#eaecdf] p-2 md:p-5 flex flex-col md:flex-row gap-5 font-sans">
        <!-- Sidebar Navigation Rail -->
        <SidebarNav active="simulator" />

        <!-- Main Content -->
        <main class="flex-1 flex flex-col gap-5 min-w-0">
            <!-- Header Section -->
            <header class="bg-white/80 backdrop-blur-md rounded-[24px] p-5 border border-[#dce0cd] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#c6ef4e] border border-[#a2c833]"></span>
                        <h1 class="text-xl font-black text-[#0e0f0c] tracking-tight">What-If Scenario Simulator</h1>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-600">Simulasi Keputusan Bisnis</span>
                    </div>
                    <p class="text-xs text-neutral-500 font-medium mt-1">Uji dampak perubahan harga jual, kapasitas produksi pengrajin, beban tetap, dan efisiensi waste terhadap profitabilitas</p>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="resetSimulation"
                        class="bg-white hover:bg-neutral-50 text-[#0e0f0c] border border-neutral-300 text-xs font-bold px-4 py-2.5 rounded-full transition cursor-pointer shadow-sm"
                    >
                        Reset Slider
                    </button>
                    <button
                        type="button"
                        @click="showSettingsModal = true"
                        class="bg-[#0e0f0c] hover:bg-neutral-800 text-white text-xs font-bold px-4 py-2.5 rounded-full transition cursor-pointer shadow-sm"
                    >
                        ⚙️ Atur Biaya Operasional
                    </button>
                </div>
            </header>

            <!-- Dynamic Lime Insight Banner -->
            <section class="bg-[#c6ef4e] rounded-[28px] p-6 border border-[#b2db3a] shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-neutral-800">Proyeksi Laba Bersih Skenario</span>
                    <div class="text-3xl font-black text-[#0e0f0c] mt-1 tabular-nums">
                        {{ formatRupiah(projectedNetProfit) }}
                    </div>
                    <p class="text-xs text-neutral-800 font-semibold mt-1">
                        Dampak skenario:
                        <span :class="netProfitDifference >= 0 ? 'text-emerald-950 font-black' : 'text-rose-950 font-black'">
                            {{ netProfitDifference >= 0 ? '+' : '' }}{{ formatRupiah(netProfitDifference) }} ({{ netProfitDifference >= 0 ? '+' : '' }}{{ netProfitPctChange }}%)
                        </span>
                        dibandingkan baseline riil saat ini.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="bg-black/10 px-4 py-2 rounded-2xl border border-black/15 text-right">
                        <div class="text-[10px] font-bold text-neutral-800 uppercase">Beban Kapasitas</div>
                        <div class="text-base font-black text-[#0e0f0c]">{{ projectedCapacityUsage }}%</div>
                    </div>
                    <div class="bg-black/10 px-4 py-2 rounded-2xl border border-black/15 text-right">
                        <div class="text-[10px] font-bold text-neutral-800 uppercase">Target Impas (BEP)</div>
                        <div class="text-base font-black text-[#0e0f0c]">{{ formatRupiah(projectedBep) }}</div>
                    </div>
                </div>
            </section>

            <!-- 2 Columns: Sliders on Left, Before vs After on Right -->
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                <!-- Sliders Panel - Col 5 -->
                <div class="lg:col-span-5 bg-white rounded-[28px] p-6 border border-[#dce0cd] shadow-sm space-y-5">
                    <div class="border-b border-neutral-100 pb-3">
                        <h2 class="text-base font-black text-[#0e0f0c]">Parameter Simulasi Interaktif</h2>
                        <p class="text-xs text-neutral-400">Geser slider untuk melihat dampak proyeksi secara instan</p>
                    </div>

                    <!-- Slider 1: Penyesuaian Harga Jual -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-neutral-700">Penyesuaian Harga Jual (Katalog / Deal)</span>
                            <span :class="priceAdjustmentPct >= 0 ? 'text-emerald-600 font-black' : 'text-rose-600 font-black'">
                                {{ priceAdjustmentPct >= 0 ? '+' : '' }}{{ priceAdjustmentPct }}%
                            </span>
                        </div>
                        <input
                            v-model.number="priceAdjustmentPct"
                            type="range"
                            min="-30"
                            max="30"
                            step="1"
                            class="w-full accent-[#0e0f0c] cursor-pointer"
                        />
                        <div class="flex justify-between text-[10px] text-neutral-400">
                            <span>-30%</span>
                            <span>Normal (0%)</span>
                            <span>+30%</span>
                        </div>
                    </div>

                    <!-- Slider 2: Volume Penjualan / Produksi -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-neutral-700">Volume Penjualan (Kuantitas Pasang)</span>
                            <span :class="volumeAdjustmentPct >= 0 ? 'text-emerald-600 font-black' : 'text-rose-600 font-black'">
                                {{ volumeAdjustmentPct >= 0 ? '+' : '' }}{{ volumeAdjustmentPct }}% ({{ projectedVolume }} pasang)
                            </span>
                        </div>
                        <input
                            v-model.number="volumeAdjustmentPct"
                            type="range"
                            min="-50"
                            max="100"
                            step="5"
                            class="w-full accent-[#0e0f0c] cursor-pointer"
                        />
                        <div class="flex justify-between text-[10px] text-neutral-400">
                            <span>-50%</span>
                            <span>Baseline</span>
                            <span>+100%</span>
                        </div>
                    </div>

                    <!-- Slider 3: Jumlah Pengrajin Sepatu -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-neutral-700">Jumlah Pengrajin Workshop</span>
                            <span class="font-black text-[#0e0f0c]">{{ simEmployeeCount }} Orang</span>
                        </div>
                        <input
                            v-model.number="simEmployeeCount"
                            type="range"
                            min="1"
                            max="15"
                            step="1"
                            class="w-full accent-[#0e0f0c] cursor-pointer"
                        />
                        <div class="flex justify-between text-[10px] text-neutral-400">
                            <span>1 Orang</span>
                            <span>Baseline: {{ props.baseline?.employeeCount || 5 }}</span>
                            <span>15 Orang</span>
                        </div>
                    </div>

                    <!-- Slider 4: Efisiensi Waste / Penekanan Cacat Bahan -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-neutral-700">Penekanan Reject QC / Waste Kulit</span>
                            <span class="text-emerald-600 font-black">Hemat {{ simWasteReductionPct }}%</span>
                        </div>
                        <input
                            v-model.number="simWasteReductionPct"
                            type="range"
                            min="0"
                            max="80"
                            step="5"
                            class="w-full accent-[#0e0f0c] cursor-pointer"
                        />
                        <div class="flex justify-between text-[10px] text-neutral-400">
                            <span>0% (Tetap)</span>
                            <span>40% QC Ketat</span>
                            <span>80% Nyaris Nol</span>
                        </div>
                    </div>
                </div>

                <!-- Before vs After Table Panel - Col 7 -->
                <div class="lg:col-span-7 bg-white rounded-[28px] p-6 border border-[#dce0cd] shadow-sm space-y-4">
                    <div class="border-b border-neutral-100 pb-3 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-black text-[#0e0f0c]">Tabel Perbandingan Dampak: Before vs After</h2>
                            <p class="text-xs text-neutral-400">Analisis metrik finansial real-time</p>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-[#f4f5ee] text-[#0e0f0c]">
                            Simulasi Aktif
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-neutral-100 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                                    <th class="py-2.5 px-3">Metrik Finansial</th>
                                    <th class="py-2.5 px-3 text-right">Kondisi Riil (Before)</th>
                                    <th class="py-2.5 px-3 text-right">Simulasi (After)</th>
                                    <th class="py-2.5 px-3 text-right">Perubahan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100">
                                <tr>
                                    <td class="py-3 px-3 font-semibold text-neutral-700">Volume Terjual</td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-500 tabular-nums">{{ props.baseline?.totalItemsSold || 0 }} Pasang</td>
                                    <td class="py-3 px-3 text-right font-black text-[#0e0f0c] tabular-nums">{{ projectedVolume }} Pasang</td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-600">
                                        {{ projectedVolume - (props.baseline?.totalItemsSold || 0) >= 0 ? '+' : '' }}{{ projectedVolume - (props.baseline?.totalItemsSold || 0) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-3 font-semibold text-neutral-700">Rata-rata Harga / Pasang</td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-500 tabular-nums">{{ formatRupiah(props.baseline?.avgPricePerUnit) }}</td>
                                    <td class="py-3 px-3 text-right font-black text-[#0e0f0c] tabular-nums">{{ formatRupiah(projectedPricePerUnit) }}</td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-600">
                                        {{ priceAdjustmentPct >= 0 ? '+' : '' }}{{ priceAdjustmentPct }}%
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-3 font-semibold text-neutral-700">Total Omzet Bersih</td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-500 tabular-nums">{{ formatRupiah(props.baseline?.netRevenue) }}</td>
                                    <td class="py-3 px-3 text-right font-black text-[#0e0f0c] tabular-nums">{{ formatRupiah(projectedRevenue) }}</td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-600">
                                        {{ projectedRevenue - (props.baseline?.netRevenue || 0) >= 0 ? '+' : '' }}{{ formatRupiah(projectedRevenue - (props.baseline?.netRevenue || 0)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-3 font-semibold text-neutral-700">Total HPP Bahan Kulit</td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-500 tabular-nums">{{ formatRupiah(props.baseline?.cogs) }}</td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-700 tabular-nums">{{ formatRupiah(projectedCogs) }}</td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-500">
                                        {{ projectedCogs - (props.baseline?.cogs || 0) >= 0 ? '+' : '' }}{{ formatRupiah(projectedCogs - (props.baseline?.cogs || 0)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-3 font-semibold text-neutral-700">Beban Operasional Tetap (Opex)</td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-500 tabular-nums">{{ formatRupiah(props.baseline?.totalFixedOpex) }}</td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-700 tabular-nums">{{ formatRupiah(projectedFixedCost) }}</td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-500">
                                        {{ projectedFixedCost - (props.baseline?.totalFixedOpex || 0) >= 0 ? '+' : '' }}{{ formatRupiah(projectedFixedCost - (props.baseline?.totalFixedOpex || 0)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-3 font-semibold text-neutral-700">Kerugian Waste Material</td>
                                    <td class="py-3 px-3 text-right font-bold text-rose-500 tabular-nums">{{ formatRupiah(props.baseline?.wasteCost) }}</td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-700 tabular-nums">{{ formatRupiah(projectedWasteCost) }}</td>
                                    <td class="py-3 px-3 text-right font-bold text-emerald-600">
                                        -{{ formatRupiah((props.baseline?.wasteCost || 0) - projectedWasteCost) }}
                                    </td>
                                </tr>
                                <tr class="bg-[#fafbf7]">
                                    <td class="py-3 px-3 font-black text-[#0e0f0c]">Laba Bersih (Net Profit)</td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-500 tabular-nums">{{ formatRupiah(props.baseline?.netProfit) }}</td>
                                    <td class="py-3 px-3 text-right font-black text-xl text-[#0e0f0c] tabular-nums">{{ formatRupiah(projectedNetProfit) }}</td>
                                    <td class="py-3 px-3 text-right font-black" :class="netProfitDifference >= 0 ? 'text-emerald-700' : 'text-rose-700'">
                                        {{ netProfitDifference >= 0 ? '+' : '' }}{{ formatRupiah(netProfitDifference) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>

        <!-- Settings Modal -->
        <div
            v-if="showSettingsModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0e0f0c]/60 backdrop-blur-sm"
            @click.self="showSettingsModal = false"
        >
            <div class="bg-white rounded-[28px] w-full max-w-lg shadow-2xl border border-[#dce0cd] overflow-hidden">
                <div class="p-6 border-b border-neutral-100 flex items-center justify-between bg-[#fafbf7]">
                    <div>
                        <h3 class="text-xl font-black text-[#0e0f0c]">Pengaturan Baseline Operasional</h3>
                        <p class="text-xs text-neutral-500 mt-0.5">Konfigurasi kapasitas dan biaya tetap bulanan workshop</p>
                    </div>
                    <button
                        type="button"
                        @click="showSettingsModal = false"
                        class="w-9 h-9 rounded-full bg-neutral-200 hover:bg-neutral-300 flex items-center justify-center text-neutral-700 cursor-pointer font-bold"
                    >
                        ✕
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold text-neutral-600 mb-1">Nama Bengkel / Workshop</label>
                        <input
                            v-model="settingsForm.workshop_name"
                            type="text"
                            class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Jumlah Pengrajin</label>
                            <input
                                v-model.number="settingsForm.employee_count"
                                type="number"
                                min="1"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-bold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Gaji per Pengrajin (Rp)</label>
                            <input
                                v-model.number="settingsForm.salary_per_employee"
                                type="number"
                                step="100000"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-bold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Sewa Bengkel & Listrik (Rp)</label>
                            <input
                                v-model.number="settingsForm.monthly_fixed_cost"
                                type="number"
                                step="100000"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-bold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Kapasitas Maksimal (Pasang/Bln)</label>
                            <input
                                v-model.number="settingsForm.monthly_max_capacity"
                                type="number"
                                min="10"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-bold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            />
                        </div>
                    </div>

                    <div class="pt-3 border-t border-neutral-100 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="showSettingsModal = false"
                            class="px-4 py-2 text-xs font-bold text-neutral-600 hover:text-neutral-900"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="submitSettings"
                            :disabled="isSaving"
                            class="bg-[#0e0f0c] hover:bg-neutral-800 text-white font-bold text-xs px-5 py-2.5 rounded-full transition cursor-pointer shadow-sm"
                        >
                            {{ isSaving ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
