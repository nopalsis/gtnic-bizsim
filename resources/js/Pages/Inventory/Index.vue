<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import SidebarNav from '@/Components/SidebarNav.vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    recentMovements: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
});

// UI State
const activeTab = ref('catalog'); // 'catalog' | 'movements'
const searchQuery = ref('');
const showWasteModal = ref(false);
const showProductionModal = ref(false);
const isSubmitting = ref(false);

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

// Filtered Products
const filteredProducts = computed(() => {
    if (!searchQuery.value) return props.products;
    const q = searchQuery.value.toLowerCase();
    return props.products.filter(
        (p) =>
            p.name?.toLowerCase().includes(q) ||
            p.sku?.toLowerCase().includes(q) ||
            p.category?.toLowerCase().includes(q)
    );
});

// Waste Form State
const wasteForm = ref({
    product_id: '',
    quantity: 1,
    movement_date: new Date().toISOString().slice(0, 10),
    notes: 'Reject QC: kulit sobek saat proses penarikan cetakan kayu (lasting)',
});

const submitWaste = () => {
    if (!wasteForm.value.product_id) {
        alert('Mohon pilih produk yang mengalami waste / cacat.');
        return;
    }
    isSubmitting.value = true;
    router.post('/inventory/waste', wasteForm.value, {
        onSuccess: () => {
            showWasteModal.value = false;
            wasteForm.value = {
                product_id: '',
                quantity: 1,
                movement_date: new Date().toISOString().slice(0, 10),
                notes: 'Reject QC: kulit sobek saat proses penarikan cetakan kayu (lasting)',
            };
            isSubmitting.value = false;
        },
        onError: () => {
            isSubmitting.value = false;
        },
    });
};

// Production In Form State
const prodForm = ref({
    product_id: '',
    quantity: 10,
    movement_date: new Date().toISOString().slice(0, 10),
    notes: 'Batch produksi baru selesai dari pengrajin workshop',
});

const submitProduction = () => {
    if (!prodForm.value.product_id) {
        alert('Mohon pilih produk yang selesai diproduksi.');
        return;
    }
    isSubmitting.value = true;
    router.post('/inventory/in', prodForm.value, {
        onSuccess: () => {
            showProductionModal.value = false;
            prodForm.value = {
                product_id: '',
                quantity: 10,
                movement_date: new Date().toISOString().slice(0, 10),
                notes: 'Batch produksi baru selesai dari pengrajin workshop',
            };
            isSubmitting.value = false;
        },
        onError: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <Head title="Inventaris Stok & Waste Tracker · OptimaBiz" />

    <div class="min-h-screen w-full bg-[#eaecdf] p-2 md:p-5 flex flex-col md:flex-row gap-5 font-sans">
        <!-- Sidebar Navigation Rail -->
        <SidebarNav active="inventory" />

        <!-- Main Content -->
        <main class="flex-1 flex flex-col gap-5 min-w-0">
            <!-- Header Section -->
            <header class="bg-white/80 backdrop-blur-md rounded-[24px] p-5 border border-[#dce0cd] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#c6ef4e] border border-[#a2c833]"></span>
                        <h1 class="text-xl font-black text-[#0e0f0c] tracking-tight">Inventaris & Waste Tracker</h1>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-600">Bahan & Produk Jadi</span>
                    </div>
                    <p class="text-xs text-neutral-500 font-medium mt-1">Pengawasan ketersediaan stok model sepatu, sandal, dan pencatatan kebocoran reject material</p>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <button
                        type="button"
                        @click="showProductionModal = true"
                        class="bg-white hover:bg-neutral-50 text-[#0e0f0c] border border-neutral-300 text-xs font-bold px-4 py-2.5 rounded-full transition flex items-center gap-1.5 cursor-pointer shadow-sm"
                    >
                        <span>+ Masuk Produksi</span>
                    </button>
                    <button
                        type="button"
                        @click="showWasteModal = true"
                        class="bg-[#0e0f0c] hover:bg-neutral-800 text-white text-xs font-bold px-4 py-2.5 rounded-full transition flex items-center gap-1.5 cursor-pointer shadow-sm"
                    >
                        <span class="text-rose-400 font-black">✕</span>
                        <span>Catat Reject / Waste</span>
                    </button>
                </div>
            </header>

            <!-- 4 Metric Cards -->
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm">
                    <span class="text-[10px] font-black uppercase tracking-wider text-neutral-400">Total Stok Fisik</span>
                    <div class="text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                        {{ props.stats?.totalStockUnits || 0 }} Unit
                    </div>
                    <div class="text-[11px] text-neutral-500 font-medium mt-1">
                        Tersedia di gudang & showroom
                    </div>
                </div>

                <div class="bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm">
                    <span class="text-[10px] font-black uppercase tracking-wider text-neutral-400">Valuasi Stok (HPP)</span>
                    <div class="text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                        {{ formatRupiah(props.stats?.totalStockValuation) }}
                    </div>
                    <div class="text-[11px] text-neutral-500 font-medium mt-1">
                        Nilai modal tertanam pada stok
                    </div>
                </div>

                <div class="bg-rose-50 rounded-[24px] p-5 border border-rose-200 shadow-sm">
                    <span class="text-[10px] font-black uppercase tracking-wider text-rose-800">Kerugian Limbah / Waste</span>
                    <div class="text-2xl font-black text-rose-900 mt-2 tabular-nums">
                        {{ formatRupiah(props.stats?.totalWasteCost) }}
                    </div>
                    <div class="text-[11px] text-rose-700 font-semibold mt-1">
                        {{ props.stats?.totalWasteUnits || 0 }} unit reject produksi tercatat
                    </div>
                </div>

                <div class="bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm">
                    <span class="text-[10px] font-black uppercase tracking-wider text-neutral-400">Peringatan Stok Rendah</span>
                    <div class="text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                        {{ props.stats?.lowStockCount || 0 }} Model
                    </div>
                    <div class="text-[11px] text-neutral-500 font-medium mt-1">
                        Segera jadwalkan antrian jahit
                    </div>
                </div>
            </section>

            <!-- Tabbed Main Card -->
            <section class="bg-white rounded-[28px] p-6 border border-[#dce0cd] shadow-sm flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <!-- Tab Switcher -->
                    <div class="flex items-center gap-2 bg-[#f4f5ee] p-1 rounded-2xl">
                        <button
                            type="button"
                            @click="activeTab = 'catalog'"
                            :class="activeTab === 'catalog' ? 'bg-white text-[#0e0f0c] shadow-sm font-bold' : 'text-neutral-500 font-semibold'"
                            class="px-4 py-2 rounded-xl text-xs transition cursor-pointer"
                        >
                            Katalog Model Produk ({{ props.products.length }})
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'movements'"
                            :class="activeTab === 'movements' ? 'bg-white text-[#0e0f0c] shadow-sm font-bold' : 'text-neutral-500 font-semibold'"
                            class="px-4 py-2 rounded-xl text-xs transition cursor-pointer"
                        >
                            Log Waste & Mutasi Stok ({{ props.recentMovements.length }})
                        </button>
                    </div>

                    <div class="w-full sm:w-64">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari SKU atau nama produk..."
                            class="w-full bg-[#f6f7f7] rounded-full px-4 py-2 text-xs font-semibold border-0 outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                        />
                    </div>
                </div>

                <!-- Tab 1: Products Catalog Table -->
                <div v-if="activeTab === 'catalog'" class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-neutral-100 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                                <th class="py-3 px-3">SKU & Model Produk</th>
                                <th class="py-3 px-3">Kategori</th>
                                <th class="py-3 px-3 text-right">HPP (Modal)</th>
                                <th class="py-3 px-3 text-right">Harga Banderol</th>
                                <th class="py-3 px-3 text-center">Stok Fisik</th>
                                <th class="py-3 px-3 text-center">Status</th>
                                <th class="py-3 px-3 text-right">Valuasi Stok</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            <tr
                                v-for="prod in filteredProducts"
                                :key="prod.id"
                                class="hover:bg-[#fafbf7] transition-colors"
                            >
                                <td class="py-3 px-3">
                                    <div class="font-bold text-[#0e0f0c]">{{ prod.name }}</div>
                                    <span class="font-mono text-[10px] text-neutral-400">{{ prod.sku }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-700">
                                        {{ prod.category }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-neutral-500 tabular-nums">
                                    {{ formatRupiah(prod.cost_price) }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-[#0e0f0c] tabular-nums">
                                    {{ formatRupiah(prod.base_price) }}
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-[#0e0f0c] tabular-nums">
                                    {{ prod.current_stock }} {{ prod.unit }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span
                                        :class="[
                                            'px-2.5 py-0.5 rounded-full text-[10px] font-bold inline-block',
                                            prod.is_low_stock
                                                ? 'bg-rose-100 text-rose-800 border border-rose-300'
                                                : 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                        ]"
                                    >
                                        {{ prod.is_low_stock ? 'Menipis (≤ ' + prod.min_stock_alert + ')' : 'Stok Aman' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-black text-[#0e0f0c] tabular-nums">
                                    {{ formatRupiah(prod.valuation) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tab 2: Stock Movements & Waste Log -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-neutral-100 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                                <th class="py-3 px-3">Tanggal</th>
                                <th class="py-3 px-3">Tipe Mutasi</th>
                                <th class="py-3 px-3">Produk / Model</th>
                                <th class="py-3 px-3 text-center">Jumlah</th>
                                <th class="py-3 px-3 text-right">Kerugian Biaya</th>
                                <th class="py-3 px-3">Catatan Insiden / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            <tr
                                v-for="m in props.recentMovements"
                                :key="m.id"
                                class="hover:bg-[#fafbf7] transition-colors"
                            >
                                <td class="py-3 px-3 text-neutral-500 font-medium">
                                    {{ m.date }}
                                </td>
                                <td class="py-3 px-3">
                                    <span
                                        :class="[
                                            'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider inline-block',
                                            m.type === 'waste'
                                                ? 'bg-rose-100 text-rose-800'
                                                : m.type === 'in'
                                                ? 'bg-emerald-100 text-emerald-800'
                                                : 'bg-neutral-100 text-neutral-700'
                                        ]"
                                    >
                                        {{ m.type === 'waste' ? 'Reject / Waste' : m.type === 'in' ? 'Produksi Masuk' : m.type }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-bold text-[#0e0f0c]">
                                    {{ m.product_name }}
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-[#0e0f0c] tabular-nums">
                                    {{ m.quantity }} {{ m.unit }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-rose-600 tabular-nums">
                                    {{ m.cost_impact > 0 ? '- ' + formatRupiah(m.cost_impact) : '-' }}
                                </td>
                                <td class="py-3 px-3 text-neutral-600 max-w-[320px] truncate" :title="m.notes">
                                    {{ m.notes || '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

        <!-- Record Waste Modal -->
        <div
            v-if="showWasteModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0e0f0c]/60 backdrop-blur-sm"
            @click.self="showWasteModal = false"
        >
            <div class="bg-white rounded-[28px] w-full max-w-lg shadow-2xl border border-[#dce0cd] overflow-hidden">
                <div class="p-6 border-b border-neutral-100 flex items-center justify-between bg-rose-50/50">
                    <div>
                        <h3 class="text-xl font-black text-rose-950">Catat Reject QC / Kerusakan Bahan</h3>
                        <p class="text-xs text-rose-700 mt-0.5">Mencatat kerugian material kulit yang langsung memotong laba bersih</p>
                    </div>
                    <button
                        type="button"
                        @click="showWasteModal = false"
                        class="w-9 h-9 rounded-full bg-white flex items-center justify-center text-neutral-700 cursor-pointer font-bold"
                    >
                        ✕
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold text-neutral-600 mb-1">Pilih Model Produk / Bahan</label>
                        <select
                            v-model="wasteForm.product_id"
                            class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                        >
                            <option value="" disabled>-- Pilih Produk --</option>
                            <option
                                v-for="p in props.products"
                                :key="p.id"
                                :value="p.id"
                            >
                                {{ p.sku }} - {{ p.name }} (Stok Saat Ini: {{ p.current_stock }})
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Jumlah Unit Rusak</label>
                            <input
                                v-model.number="wasteForm.quantity"
                                type="number"
                                min="1"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-bold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Tanggal Insiden</label>
                            <input
                                v-model="wasteForm.movement_date"
                                type="date"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-neutral-600 mb-1">Penyebab Reject QC / Keterangan</label>
                        <select
                            v-model="wasteForm.notes"
                            class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                        >
                            <option value="Reject QC: kulit sobek saat penarikan cetakan kayu (lasting)">Reject QC: kulit sobek saat penarikan cetakan kayu (lasting)</option>
                            <option value="Cacat lem: sol terkelupas akibat suhu oven pemanas tidak stabil">Cacat lem: sol terkelupas akibat suhu oven pemanas tidak stabil</option>
                            <option value="Kulit berkerut parah & serat pecah saat proses pengeleman upper">Kulit berkerut parah & serat pecah saat proses pengeleman upper</option>
                            <option value="Bercak jamur pada lembaran kulit akibat kelembapan gudang workshop">Bercak jamur pada lembaran kulit akibat kelembapan gudang workshop</option>
                            <option value="Reject jahitan: jarum mesin jahit patah & merusak tekstur kulit finish">Reject jahitan: jarum mesin jahit patah & merusak tekstur kulit finish</option>
                            <option value="Goresan pisau seset terlalu dalam pada material kulit pull-up">Goresan pisau seset terlalu dalam pada material kulit pull-up</option>
                        </select>
                    </div>

                    <div class="pt-3 border-t border-neutral-100 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="showWasteModal = false"
                            class="px-4 py-2 text-xs font-bold text-neutral-600 hover:text-neutral-900"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="submitWaste"
                            :disabled="isSubmitting"
                            class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs px-5 py-2.5 rounded-full transition cursor-pointer shadow-sm"
                        >
                            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Catatan Waste' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Record Production In Modal -->
        <div
            v-if="showProductionModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0e0f0c]/60 backdrop-blur-sm"
            @click.self="showProductionModal = false"
        >
            <div class="bg-white rounded-[28px] w-full max-w-lg shadow-2xl border border-[#dce0cd] overflow-hidden">
                <div class="p-6 border-b border-neutral-100 flex items-center justify-between bg-emerald-50/50">
                    <div>
                        <h3 class="text-xl font-black text-emerald-950">Catat Hasil Produksi Masuk</h3>
                        <p class="text-xs text-emerald-700 mt-0.5">Menambah stok produk selesai dari workshop pengrajin</p>
                    </div>
                    <button
                        type="button"
                        @click="showProductionModal = false"
                        class="w-9 h-9 rounded-full bg-white flex items-center justify-center text-neutral-700 cursor-pointer font-bold"
                    >
                        ✕
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold text-neutral-600 mb-1">Pilih Model Produk</label>
                        <select
                            v-model="prodForm.product_id"
                            class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                        >
                            <option value="" disabled>-- Pilih Produk --</option>
                            <option
                                v-for="p in props.products"
                                :key="p.id"
                                :value="p.id"
                            >
                                {{ p.sku }} - {{ p.name }} (Stok Saat Ini: {{ p.current_stock }})
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Jumlah Unit Selesai</label>
                            <input
                                v-model.number="prodForm.quantity"
                                type="number"
                                min="1"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-bold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Tanggal Masuk</label>
                            <input
                                v-model="prodForm.movement_date"
                                type="date"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-neutral-600 mb-1">Keterangan Batch</label>
                        <input
                            v-model="prodForm.notes"
                            type="text"
                            class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                        />
                    </div>

                    <div class="pt-3 border-t border-neutral-100 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="showProductionModal = false"
                            class="px-4 py-2 text-xs font-bold text-neutral-600 hover:text-neutral-900"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="submitProduction"
                            :disabled="isSubmitting"
                            class="bg-[#0e0f0c] hover:bg-neutral-800 text-white font-bold text-xs px-5 py-2.5 rounded-full transition cursor-pointer shadow-sm"
                        >
                            {{ isSubmitting ? 'Menyimpan...' : 'Tambah ke Stok Gudang' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
