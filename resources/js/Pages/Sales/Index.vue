<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import SidebarNav from '@/Components/SidebarNav.vue';

const props = defineProps({
    transactions: {
        type: Array,
        default: () => [],
    },
    metrics: {
        type: Object,
        default: () => ({}),
    },
    availableProducts: {
        type: Array,
        default: () => [],
    },
});

// UI State
const searchQuery = ref('');
const showNewOrderModal = ref(false);
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

// Filtered Transactions
const filteredTransactions = computed(() => {
    if (!searchQuery.value) return props.transactions;
    const q = searchQuery.value.toLowerCase();
    return props.transactions.filter(
        (tx) =>
            tx.invoice_number?.toLowerCase().includes(q) ||
            tx.customer_name?.toLowerCase().includes(q) ||
            tx.sales_person_name?.toLowerCase().includes(q)
    );
});

// New Order Form State
const newOrder = ref({
    customer_name: '',
    sales_person_name: 'Kang Asep (Sales Pengrajin)',
    payment_method: 'Transfer Bank (BCA)',
    notes: '',
    items: [
        { product_id: '', quantity: 1, base_price: 0, cost_price: 0, deal_price: 0 },
    ],
});

const onProductSelect = (index, productId) => {
    const selected = props.availableProducts.find((p) => p.id === Number(productId));
    if (selected) {
        newOrder.value.items[index].base_price = selected.base_price;
        newOrder.value.items[index].cost_price = selected.cost_price;
        newOrder.value.items[index].deal_price = selected.base_price;
    }
};

const addItemRow = () => {
    newOrder.value.items.push({
        product_id: '',
        quantity: 1,
        base_price: 0,
        cost_price: 0,
        deal_price: 0,
    });
};

const removeItemRow = (index) => {
    if (newOrder.value.items.length > 1) {
        newOrder.value.items.splice(index, 1);
    }
};

const computedGross = computed(() => {
    return newOrder.value.items.reduce((acc, it) => acc + (it.base_price * it.quantity), 0);
});

const computedNet = computed(() => {
    return newOrder.value.items.reduce((acc, it) => acc + (it.deal_price * it.quantity), 0);
});

const computedDiscount = computed(() => {
    return Math.max(0, computedGross.value - computedNet.value);
});

const submitOrder = () => {
    if (!newOrder.value.customer_name) {
        alert('Mohon isi nama pelanggan atau nama toko rekanan.');
        return;
    }
    const hasInvalidItem = newOrder.value.items.some((it) => !it.product_id || it.quantity <= 0);
    if (hasInvalidItem) {
        alert('Mohon lengkapi produk dan kuantitas pesanan.');
        return;
    }

    isSubmitting.value = true;
    router.post('/sales', newOrder.value, {
        onSuccess: () => {
            showNewOrderModal.value = false;
            newOrder.value = {
                customer_name: '',
                sales_person_name: 'Kang Asep (Sales Pengrajin)',
                payment_method: 'Transfer Bank (BCA)',
                notes: '',
                items: [{ product_id: '', quantity: 1, base_price: 0, cost_price: 0, deal_price: 0 }],
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
    <Head title="Sales Intelligence & Negosiasi Deal Price · OptimaBiz" />

    <div class="min-h-screen w-full bg-[#eaecdf] p-2 md:p-5 flex flex-col md:flex-row gap-5 font-sans">
        <!-- Sidebar Navigation Rail -->
        <SidebarNav active="sales" />

        <!-- Main Content -->
        <main class="flex-1 flex flex-col gap-5 min-w-0">
            <!-- Header Section -->
            <header class="bg-white/80 backdrop-blur-md rounded-[24px] p-5 border border-[#dce0cd] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#c6ef4e] border border-[#a2c833]"></span>
                        <h1 class="text-xl font-black text-[#0e0f0c] tracking-tight">Sales Intelligence & Deal Tracker</h1>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-600">Pelacakan Negosiasi Harga</span>
                    </div>
                    <p class="text-xs text-neutral-500 font-medium mt-1">Analisis selisih harga banderol (base price) vs harga kesepakatan riil (deal price)</p>
                </div>

                <button
                    type="button"
                    @click="showNewOrderModal = true"
                    class="bg-[#0e0f0c] hover:bg-neutral-800 text-white text-xs font-bold px-5 py-2.5 rounded-full transition flex items-center gap-2 shadow-sm cursor-pointer"
                >
                    <span class="text-[#c6ef4e] text-base leading-none">+</span>
                    <span>Catat Pesanan / Faktur Baru</span>
                </button>
            </header>

            <!-- 4 Metric Cards -->
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm">
                    <span class="text-[10px] font-black uppercase tracking-wider text-neutral-400">Total Omzet Bersih</span>
                    <div class="text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                        {{ formatRupiah(props.metrics?.totalNetRevenue) }}
                    </div>
                    <div class="text-[11px] text-neutral-500 font-medium mt-1">
                        Dari {{ props.metrics?.totalTransactions || 0 }} rekaman faktur
                    </div>
                </div>

                <div class="bg-[#c6ef4e] rounded-[24px] p-5 border border-[#b2db3a] shadow-sm">
                    <span class="text-[10px] font-black uppercase tracking-wider text-neutral-800">Total Potongan Negosiasi</span>
                    <div class="text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                        {{ formatRupiah(props.metrics?.totalDiscount) }}
                    </div>
                    <div class="text-[11px] text-neutral-800 font-semibold mt-1">
                        Rata-rata diskon {{ props.metrics?.avgDiscountPercent || 0 }}%
                    </div>
                </div>

                <div class="bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm">
                    <span class="text-[10px] font-black uppercase tracking-wider text-neutral-400">Omzet Kotor (Banderol)</span>
                    <div class="text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                        {{ formatRupiah(props.metrics?.totalGrossRevenue) }}
                    </div>
                    <div class="text-[11px] text-neutral-500 font-medium mt-1">
                        Jika semua terjual harga katalog
                    </div>
                </div>

                <div class="bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm">
                    <span class="text-[10px] font-black uppercase tracking-wider text-neutral-400">Disiplin Negosiasi</span>
                    <div class="text-2xl font-black text-[#0e0f0c] mt-2">
                        {{ (props.metrics?.avgDiscountPercent || 0) <= 15 ? 'Aman' : 'Perlu Pengawasan' }}
                    </div>
                    <div class="text-[11px] text-neutral-500 font-medium mt-1">
                        Batas aman grosir &lt; 15%
                    </div>
                </div>
            </section>

            <!-- Table of Transactions -->
            <section class="bg-white rounded-[28px] p-6 border border-[#dce0cd] shadow-sm flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-black text-[#0e0f0c]">Daftar Riwayat Penjualan & Diskon Kesepakatan</h2>
                        <p class="text-xs text-neutral-400">Rincian faktur riil sepatu & sandal kulit UMKM</p>
                    </div>

                    <div class="w-full sm:w-72">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nomor invoice, pelanggan, atau sales..."
                            class="w-full bg-[#f6f7f7] rounded-full px-4 py-2 text-xs font-semibold border-0 outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-neutral-100 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                                <th class="py-3 px-3">Faktur & Tanggal</th>
                                <th class="py-3 px-3">Pelanggan / Toko</th>
                                <th class="py-3 px-3">Sales & Pembayaran</th>
                                <th class="py-3 px-3">Item Pesanan</th>
                                <th class="py-3 px-3 text-right">Harga Banderol</th>
                                <th class="py-3 px-3 text-center">Diskon Kesepakatan</th>
                                <th class="py-3 px-3 text-right">Nilai Faktur Riil</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            <tr
                                v-for="tx in filteredTransactions"
                                :key="tx.id"
                                class="hover:bg-[#fafbf7] transition-colors"
                            >
                                <td class="py-3 px-3">
                                    <div class="font-bold text-[#0e0f0c]">{{ tx.invoice_number }}</div>
                                    <div class="text-[10px] text-neutral-400">{{ tx.transaction_date }}</div>
                                </td>
                                <td class="py-3 px-3 font-bold text-[#0e0f0c]">
                                    {{ tx.customer_name }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-medium text-neutral-700">{{ tx.sales_person_name }}</div>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-600 inline-block mt-0.5">
                                        {{ tx.payment_method }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 max-w-[280px]">
                                    <div class="space-y-1">
                                        <div
                                            v-for="item in tx.items"
                                            :key="item.id"
                                            class="text-[11px] text-neutral-700"
                                        >
                                            <span class="font-bold">{{ item.product_name }}</span>
                                            <span class="text-neutral-500"> (x{{ item.quantity }} {{ item.unit }})</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-right text-neutral-500">
                                    {{ formatRupiah(tx.total_amount) }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span
                                        :class="[
                                            'px-2.5 py-0.5 rounded-full text-[10px] font-bold',
                                            tx.discount_percent === 0
                                                ? 'bg-neutral-100 text-neutral-600'
                                                : tx.discount_percent <= 12
                                                ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                                : 'bg-amber-50 text-amber-800 border border-amber-200'
                                        ]"
                                    >
                                        {{ tx.discount_percent === 0 ? 'Harga Normal' : `Diskon ${tx.discount_percent}% (${formatRupiah(tx.total_discount)})` }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-black text-[#0e0f0c]">
                                    {{ formatRupiah(tx.net_amount) }}
                                </td>
                            </tr>
                            <tr v-if="filteredTransactions.length === 0">
                                <td colspan="7" class="text-center py-8 text-neutral-400">
                                    Tidak ada data transaksi yang cocok.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

        <!-- New Order Modal -->
        <div
            v-if="showNewOrderModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0e0f0c]/60 backdrop-blur-sm"
            @click.self="showNewOrderModal = false"
        >
            <div class="bg-white rounded-[28px] w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl border border-[#dce0cd] overflow-hidden">
                <div class="p-6 border-b border-neutral-100 flex items-center justify-between bg-[#fafbf7]">
                    <div>
                        <h3 class="text-xl font-black text-[#0e0f0c]">Catat Pesanan Sepatu / Sandal</h3>
                        <p class="text-xs text-neutral-500 mt-0.5">Input transaksi baru dengan negosiasi harga kesepakatan riil</p>
                    </div>
                    <button
                        type="button"
                        @click="showNewOrderModal = false"
                        class="w-9 h-9 rounded-full bg-neutral-200 hover:bg-neutral-300 flex items-center justify-center text-neutral-700 cursor-pointer font-bold"
                    >
                        ✕
                    </button>
                </div>

                <div class="p-6 overflow-y-auto space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Nama Pelanggan / Toko Rekanan *</label>
                            <input
                                v-model="newOrder.customer_name"
                                type="text"
                                placeholder="Contoh: Toko Sepatu Makmur / Pak Hendra"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Sales Person *</label>
                            <select
                                v-model="newOrder.sales_person_name"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            >
                                <option value="Kang Asep (Sales Pengrajin)">Kang Asep (Sales Pengrajin)</option>
                                <option value="Pak Bambang (Workshop Head)">Pak Bambang (Workshop Head)</option>
                                <option value="Rian (Wholesale & Store)">Rian (Wholesale & Store)</option>
                                <option value="Siti (Admin Toko & Reseller)">Siti (Admin Toko & Reseller)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Metode Pembayaran *</label>
                            <select
                                v-model="newOrder.payment_method"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            >
                                <option value="Transfer Bank (BCA)">Transfer Bank (BCA)</option>
                                <option value="Transfer Bank (Mandiri)">Transfer Bank (Mandiri)</option>
                                <option value="QRIS">QRIS</option>
                                <option value="Tunai">Tunai</option>
                                <option value="Tempo / Termin 14 Hari">Tempo / Termin 14 Hari</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Catatan Tambahan</label>
                            <input
                                v-model="newOrder.notes"
                                type="text"
                                placeholder="Catatan negosiasi / instruksi kirim"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            />
                        </div>
                    </div>

                    <!-- Item Rows -->
                    <div class="border-t border-neutral-100 pt-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-xs font-black text-[#0e0f0c]">Item Produk & Negosiasi Deal Price</h4>
                            <button
                                type="button"
                                @click="addItemRow"
                                class="text-xs font-bold text-[#0e0f0c] hover:underline cursor-pointer"
                            >
                                + Tambah Model
                            </button>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="(item, idx) in newOrder.items"
                                :key="idx"
                                class="p-3 bg-[#fafbf7] rounded-2xl border border-neutral-200 space-y-2"
                            >
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                                    <div class="sm:col-span-6">
                                        <label class="text-[10px] font-bold text-neutral-500">Pilih Model Produk</label>
                                        <select
                                            v-model="item.product_id"
                                            @change="onProductSelect(idx, item.product_id)"
                                            class="w-full bg-white rounded-lg px-2.5 py-1.5 text-xs font-semibold border border-neutral-200 outline-none"
                                        >
                                            <option value="" disabled>-- Pilih Produk --</option>
                                            <option
                                                v-for="p in props.availableProducts"
                                                :key="p.id"
                                                :value="p.id"
                                            >
                                                {{ p.sku }} - {{ p.name }} (Stok: {{ p.current_stock }})
                                            </option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="text-[10px] font-bold text-neutral-500">Jumlah (Qty)</label>
                                        <input
                                            v-model.number="item.quantity"
                                            type="number"
                                            min="1"
                                            class="w-full bg-white rounded-lg px-2.5 py-1.5 text-xs font-bold border border-neutral-200 outline-none text-center"
                                        />
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label class="text-[10px] font-bold text-neutral-500">Deal Price (Riil)</label>
                                        <input
                                            v-model.number="item.deal_price"
                                            type="number"
                                            step="1000"
                                            class="w-full bg-white rounded-lg px-2.5 py-1.5 text-xs font-black border border-neutral-200 outline-none"
                                        />
                                    </div>
                                    <div class="sm:col-span-1 text-right pt-4">
                                        <button
                                            type="button"
                                            @click="removeItemRow(idx)"
                                            class="text-rose-500 hover:text-rose-700 text-xs font-bold"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </div>
                                <div v-if="item.base_price > 0" class="text-[10px] flex items-center justify-between text-neutral-500 pt-1 border-t border-neutral-100">
                                    <span>Banderol: {{ formatRupiah(item.base_price) }} (HPP: {{ formatRupiah(item.cost_price) }})</span>
                                    <span :class="item.deal_price < item.cost_price ? 'text-rose-600 font-bold' : 'text-emerald-700'">
                                        {{ item.deal_price < item.cost_price ? '⚠️ Di bawah HPP!' : 'Subtotal: ' + formatRupiah(item.deal_price * item.quantity) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary & Submit -->
                    <div class="bg-[#f6f7f7] rounded-2xl p-4 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] text-neutral-500">Banderol: {{ formatRupiah(computedGross) }} | Diskon: {{ formatRupiah(computedDiscount) }}</div>
                            <div class="text-sm font-black text-[#0e0f0c]">Total Bersih: {{ formatRupiah(computedNet) }}</div>
                        </div>
                        <button
                            type="button"
                            @click="submitOrder"
                            :disabled="isSubmitting"
                            class="bg-[#0e0f0c] hover:bg-neutral-800 text-white font-bold text-xs px-5 py-2.5 rounded-full transition cursor-pointer"
                        >
                            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Faktur Penjualan' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
